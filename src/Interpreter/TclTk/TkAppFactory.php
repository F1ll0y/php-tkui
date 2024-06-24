<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Tkui\Exceptions\UnsupportedOSException;
use Tkui\Interfaces\AppFactoryInterface;
use Tkui\Interfaces\EnvironmentInterface;
use Tkui\System\FFILoader;
use Tkui\System\OS;
use Tkui\System\OSDetection;

/**
 * Tk implementation of Application Factory.
 */
class TkAppFactory implements AppFactoryInterface
{
    const TCL_HEADER = 'tcl86.h';
    const TK_HEADER = 'tk86.h';

    private readonly string $defaultTclHeader;
    private readonly string $defaultTkHeader;
    private readonly OS $os;

    /**
     * @param string $appName The application name (or class name in some desktop environments).
     * @param OS|null $os The operating system instance or detection will be used.
     * @throws UnsupportedOSException
     */
    public function __construct(
        private string $appName,
        ?OS $os = null
    ) {
        $this->os = $os ?? OSDetection::detect();
        $this->defaultTclHeader = $this->getHeaderPath(self::TCL_HEADER);
        $this->defaultTkHeader = $this->getHeaderPath(self::TK_HEADER);
    }

    protected function getHeaderPath(string $file): string
    {
        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . 'headers'
            . DIRECTORY_SEPARATOR
            . $file
        ;
    }

    /**
     * @inheritdoc
     */
    public function createFromEnvironment(EnvironmentInterface $env): TkApplication
    {
        $osFamily = strtoupper($this->os->family());

        if (($libTcl = $env->getValue("{$osFamily}_LIB_TCL")) === null) {
            $libTcl = $this->getDefaultTclLib();
        }
        if (($libTk = $env->getValue("{$osFamily}_LIB_TK")) === null) {
            $libTk = $this->getDefaultTkLib();
        }

        $interpreter = $this->createTcl(
                $env->getValue('TCL_HEADER', $this->defaultTclHeader),
                $libTcl
            )
            ->createInterpreter();

        if (($debug = (bool) $env->getValue('DEBUG'))) {
            $logger = $this->createLogger($env->getValue('DEBUG_LOG', 'php://stdout'));
        }

        if ($debug) {
            $interpreter->setLogger($logger->withName('interpreter'));
        }

        $tk = $this->createTk(
            $interpreter,
            $env->getValue('TK_HEADER', $this->defaultTkHeader),
            $libTk
        );
        
        $app = $this->createTkApplication($tk, $env->getValue('APP_NAME', $this->appName));

        if ($debug) {
            $app->setLogger($logger->withName('app'));
        }

        $app->init();

        if (($theme = $env->getValue('THEME', 'auto'))) {
            $app->getThemeManager()->useTheme($this->getTheme($theme));
        }

        return $app;
    }

    protected function createTcl(string $header, string $sharedLib): Tcl
    {
        $loader = new FFILoader($header, $sharedLib);
				
        return new Tcl($loader->load());
    }

    protected function createTk(Interpreter $tclInterp, string $header, string $sharedLib): Tk
    {
        $loader = new FFILoader($header, $sharedLib);
        return new Tk($loader->load(), $tclInterp);
    }

    /**
     * @inheritdoc
     */
    public function create(): TkApplication
    {
        $interp = $this->createTcl(self::TCL_HEADER, $this->getDefaultTclLib())->createInterpreter();
        $tk = $this->createTk($interp, self::TK_HEADER, $this->getDefaultTkLib());
        
        $app = $this->createTkApplication($tk, $this->appName);
        $app->init();
        
        return $app;
    }

    protected function createTkApplication(Tk $tk, string $appName): TkApplication
    {
        return new TkApplication($tk, [
            '-name' => $appName,
        ]);
    }

    protected function createLogger(string $file): Logger
    {
        $log = new Logger('php-gui');
        $formatter = new LineFormatter(dateFormat: 'Y-m-d H:i:s', ignoreEmptyContextAndExtra: true);
        $stream = new StreamHandler($file, Logger::DEBUG);
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);
        return $log;
    }

    protected function getDefaultTclLib(): string
    {
        return $this->os->tclSharedLib();
    }

    protected function getDefaultTkLib(): string
    {
        return $this->os->tkSharedLib();
    }

    protected function getTheme(string $theme): string
    {
        return strtolower($theme) === 'auto'
            ? $this->os->defaultThemeName() : $theme;
    }
}
