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

namespace Moodle\Composer\Plugin\Scaffold;

use Composer\Composer;
use Composer\IO\IOInterface;
use Moodle\Composer\Plugin\Scaffold\Scaffolding\Generator;

/**
 * Service to scaffold Moodle core files.
 */
class Scaffolder
{
    /**
     * Event name dispatched before scaffolding starts.
     */
    public const PRE_MOODLE_SCAFFOLD = 'moodle-pre-scaffold';

    /**
     * Event name dispatched after scaffolding ends.
     */
    public const POST_MOODLE_SCAFFOLD = 'moodle-post-scaffold';

    /**
     * Constructor.
     *
     * @param Composer $composer The Composer service.
     * @param IOInterface $io The Composer I/O service.
     */
    public function __construct(
        /** @var Composer The Composer service. */
        protected Composer $composer,
        /** @var IOInterface The Composer I/O service. */
        protected IOInterface $io,
    ) {
    }

    /**
     * Perform scaffold tasks for Moodle.
     *
     * @return void
     */
    public function scaffold(): void
    {
        $dispatcher = $this->composer->getEventDispatcher();

        $dispatcher->dispatchScript(self::PRE_MOODLE_SCAFFOLD);

        $this->io->write('Scaffolding Moodle core files...');

        // Scaffolding logic goes here.

        // Generate the Moodle autoload reference file.
        $generator = new Generator\ConfigFile($this->composer, $this->io);
        $generator->generate();

        $this->io->write('Moodle core files scaffolded successfully.');
        $dispatcher->dispatchScript(self::POST_MOODLE_SCAFFOLD);
    }

    /**
     * Generate the Moodle configuration file.
     *
     * @return void
     */
    public function generateConfigurationFile(): void
    {
        $this->io->write('Generating Moodle configuration file...');

        // Configuration file generation logic goes here.
        // Ask for site details (site name, admin user, password, etc).
        // Create the config.php file if not exists.
    }
}
