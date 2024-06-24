<?php declare(strict_types=1);

namespace Tkui\Interfaces;

/**
 * The application factory.
 */
interface AppFactoryInterface
{
    /**
     * Creates a new application instance based on environment state.
     */
    public function createFromEnvironment(EnvironmentInterface $env): ApplicationInterface;

    /**
     * Creates an application with default values.
     */
    public function create(): ApplicationInterface;
}