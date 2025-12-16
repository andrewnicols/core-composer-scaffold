<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace Moodle\Composer\Plugin\Scaffold\Scaffolding\Generator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

/**
 * Service to scaffold Moodle core files.
 */
abstract class BaseGenerator
{
    /**
     * Constructor.
     *
     * @param Composer $composer The Composer service.
     * @param IOInterface $io The Composer I/O service.
     */
    final public function __construct(
        /** @var Composer The Composer service. */
        protected Composer $composer,
        /** @var IOInterface The Composer I/O service. */
        protected IOInterface $io,
    ) {
    }

    /**
     * Get the root package name.
     *
     * @return string The root package name.
     */
    protected function rootPackageName(): string
    {
        return $this->composer->getPackage()->getName();
    }

    protected function getRootPackagePath(): string
    {
        return (new Filesystem())->normalizePath(
            dirname($this->getVendorPath())
        );
    }

    /**
     * Get the Moodle installation path.
     *
     * @return string The Moodle installation path.
     */
    protected function getMoodlePath(): string
    {
        return dirname($this->getVendorPath()) . '/' . $this->getInstallDirectory();
    }

    /**
     * Get the vendor directory path.
     *
     * @return string The vendor directory path.
     */
    protected function getVendorPath(): string
    {
        $vendor = $this->composer->getConfig()->get('vendor-dir');

        return (new Filesystem())->normalizePath(realpath($vendor));
    }

    /**
     * Get the name of the installation directory.
     *
     * @return string The installation directory.
     */
    protected function getInstallDirectory(): string
    {
        $extra = $this->composer->getPackage()->getExtra();
        return $extra['install-path'] ?? 'moodle/';
    }

    /**
     * Calculate the relative path between two points.
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    protected function calculateRelativePath(
        string $from,
        string $to,
        bool $directories = false,
    ): string {
        $fs = new Filesystem();
        return $fs->findShortestPath($from, $to, $directories);
    }

    /**
     * Generate the file.
     *
     * @return void
     */
    abstract public function generate(): void;
}
