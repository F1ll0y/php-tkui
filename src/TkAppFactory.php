<?php declare(strict_types=1);

namespace TclTk;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Tk implementation of Application Factory.
 */
class TkAppFactory implements AppFactory
{
    private DotEnv $dotEnv;

    /**
     * @param string $envFile A filename with environment settings.
     */
    public function __construct(string $envFile = '')
    {
        if ($envFile === '') {
            $this->dotEnv = new DotEnv(dirname(__DIR__));
        } else {
            $this->dotEnv = new DotEnv(dirname($envFile), basename($envFile));
        }
    }

    protected function loadEnv(array $config): void
    {
        $this->dotEnv->loadAndMergeWith($config);
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment(): Environment
    {
        return $this->dotEnv;
    }

    /**
     * @inheritdoc
     */
    public function create(array $config = []): TkApplication
    {
        $this->loadEnv($config);
        $env = $this->getEnvironment();

        $loader = new FFILoader();
        $tcl = new Tcl($loader->loadTcl());
        $interp = $tcl->createInterp();

        if (($debug = (bool) $env->getEnv('DEBUG'))) {
            $logger = $this->createLogger($env->getEnv('DEBUG_LOG', 'php://stdout'));
        }

        if ($debug) {
            $interp->setLogger($logger->withName('interp'));
        }

        $tk = new Tk($loader->loadTk(), $interp);
        
        $app = new TkApplication($tk);
        if ($debug) {
            $app->setLogger($logger->withName('app'));
        }
        $app->init();

        if (($theme = $env->getEnv('THEME'))) {
            $app->getThemeManager()->useTheme($theme);
        }

        return $app;
    }

    protected function createLogger(string $file): Logger
    {
        $log = new Logger('php-gui');
        // TODO: PHP8 use named arguments.
        $formatter = new LineFormatter(null, 'Y-m-d H:i:s', false, true);
        $stream = new StreamHandler($file, Logger::DEBUG);
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);
        return $log;
    }
}