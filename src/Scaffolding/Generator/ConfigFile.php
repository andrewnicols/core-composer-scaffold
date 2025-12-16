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

/**
 * Moodle Config File Generator.
 */
class ConfigFile extends BaseGenerator
{
    /** @var array Database configuration */
    protected array $databaseconfig = [];

    /** @var string The wwwroot URL */
    protected string $wwwroot = '';

    /** @var string The dataroot path */
    protected string $dataroot = '';

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function generate(): void
    {
        $this->io->write("- <info>Generating Moodle configuration file...</info>", false);
        file_put_contents($this->getConfigFilePath(), $this->getTemplateContent());
        $this->io->write("<info> done.</info>");
    }

    /**
     * Get the path to the Moodle configuration file.
     *
     * @return string
     */
    protected function getConfigFilePath(): string
    {
        return $this->getRootPackagePath() . '/config.php';
    }

    /**
     * Check if the config file already exists.
     *
     * @return bool
     */
    public function checkFileExists(): bool
    {
        return file_exists($this->getConfigFilePath());
    }

    /**
     * Set the database config.
     *
     * @param string $dbtype
     * @param string $dbhost
     * @param string $dbname
     * @param string $dbuser
     * @param string $dbpass
     * @param string $prefix
     * @return self
     */
    public function setDatabaseConfig(
        string $dbtype,
        string $dbhost,
        string $dbname,
        string $dbuser,
        string $dbpass,
        string $prefix = 'mdl_',
    ): static {
        $this->databaseconfig = [
            'dbtype' => $dbtype,
            'dbhost' => $dbhost,
            'dbname' => $dbname,
            'dbuser' => $dbuser,
            'dbpass' => $dbpass,
            'prefix' => $prefix,
        ];

        return $this;
    }

    /**
     * Set the site config.
     *
     * @param string $wwwroot
     * @param string $dataroot
     * @return self
     */
    public function setSiteConfig(
        string $wwwroot,
        string $dataroot,
    ): static {
        $this->wwwroot = $wwwroot;
        $this->dataroot = $dataroot;

        return $this;
    }

    protected function getTemplateContent(): string
    {
        return <<<TEMPLATE
        <?php

        /**
         * This is the Moodle configuration file.
         *
         * For documentation see https://docs.moodle.org/en/Configuration_file
         */

        unset(\$CFG);
        global \$CFG;
        \$CFG = new stdClass();

        \$CFG->dbtype    = '{$this->databaseconfig['dbtype']}';
        \$CFG->dblibrary = 'native';
        \$CFG->dbhost    = '{$this->databaseconfig['dbhost']}';
        \$CFG->dbname    = '{$this->databaseconfig['dbname']}';
        \$CFG->dbuser    = '{$this->databaseconfig['dbuser']}';
        \$CFG->dbpass    = '{$this->databaseconfig['dbpass']}';
        \$CFG->prefix    = '{$this->databaseconfig['prefix']}';

        \$CFG->dboptions = array (
          'dbpersist' => 0,
          'dbport' => '',
          'dbsocket' => '',
        );

        \$CFG->wwwroot   = '{$this->wwwroot}';
        \$CFG->dataroot  = '{$this->dataroot}';

        // Note: Do *not* include setup.php here.
        // For Composer-based installations, it is included by the shim config.php file.

        TEMPLATE;
    }
}
