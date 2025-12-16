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

        $this->io->write($this->asciiHeader());
        $this->io->write('<info>Scaffolding Moodle core files...</info>');

        // Generate the Moodle Configuration Shim file.
        (new Generator\ShimConfigFile($this->composer, $this->io))->generate();

        $configFile = new Generator\ConfigFile($this->composer, $this->io);
        if ($configFile->checkFileExists()) {
            $this->io->write('- <comment>Configuration file already exists. Skipping generation.</comment>');
        } else {
            $this->generateConfigurationFile();
        }

        $this->io->write('');
        $this->io->write('<info>Moodle core files scaffolded successfully.</info>');
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

        $configFile = new Generator\ConfigFile($this->composer, $this->io);

        if (!$this->io->isInteractive()) {
            $this->io->write('<error>Non-interactive mode detected. Skipping configuration file generation to avoid incomplete setup.</error>');
            return;
        }

        if ($configFile->checkFileExists()) {
            $this->io->write('<warning>Configuration file already exists. Aborting to prevent overwriting existing configuration.</warning>');
            $overwrite = $this->io->askConfirmation(
                '<question>Do you want to overwrite the existing configuration file? (y/N) </question>',
                 false
            );

            if ($overwrite) {
                $this->io->write('<warning>Overwriting existing configuration file as per user request.</warning>');
            } else {
                $this->io->write('<error>Aborting configuration file generation.</error>');
                return;
            }
        }

        // Ask for site details (site name, admin user, password, etc).
        $dbdriver = $this->io->select(
            'What database driver are you using?',
            [
                'mariadb' => 'MariaDB (mariadb)',
                'mysqli' => 'MySQL Improved (mysqli)',
                'pgsql' => 'PostgreSQL (pgsql)',
                'sqlsrv' => 'Microsoft SQL Server (sqlsrv)',
                'auroramysql' => 'Amazon Aurora MySQL (auroramysql)',
            ],
            'pgsql',
        );

        $dbuser = $this->io->askAndHideAnswer('Enter the database username: ') ?? '';
        $dbpass = $this->io->askAndHideAnswer('Enter the database password: ') ?? '';
        $dbname = $this->io->askAndValidate(
            'Enter the database name: ',
            function ($answer)  {
                if (empty($answer)) {
                    throw new \RuntimeException('Database name cannot be empty.');
                }
                return $answer;
            },
        );

        $dbhost = $this->io->ask('Enter the database host (default: localhost): ', 'localhost');
        $dbprefix = $this->io->ask('Enter the database table prefix (default: mdl_): ', 'mdl_');

        $wwwroot = $this->io->askAndValidate(
            'Enter the web root URL (for example, https://moodle.example.com): ',
            function ($answer) {
                if (empty($answer) || !filter_var($answer, FILTER_VALIDATE_URL)) {
                    throw new \RuntimeException('Please enter a valid URL for the web root.');
                }
                return rtrim($answer, '/');
            },
        );
        $dataroot = $this->io->ask('Enter the Moodle data directory path (default: moodledata): ', 'moodledata');

        $configFile->setDatabaseConfig(
            dbtype: $dbdriver,
            dbuser: $dbuser,
            dbpass: $dbpass,
            dbname: $dbname,
            dbhost: $dbhost,
            prefix: $dbprefix,
        );

        $configFile->setSiteConfig(
            wwwroot: $wwwroot,
            dataroot: $dataroot,
        );

        if (!file_exists($dataroot)) {
            mkdir($dataroot, 0770, true);
            $this->io->write("Created dataroot directory at: {$dataroot}");
        }

        $configFile->generate();
        $this->io->write('Moodle configuration file generated successfully.');
    }

    protected function asciiHeader(): string
    {
        return <<<HEADER
         __  __                 _ _
        |  \/  | ___   ___   __| | | ___
        | |\/| |/ _ \ / _ \ / _` | |/ _ \
        | |  | | (_) | (_) | (_| | |  __/
        |_|  |_|\___/ \___/ \__,_|_|\___|

        HEADER;
    }
}
