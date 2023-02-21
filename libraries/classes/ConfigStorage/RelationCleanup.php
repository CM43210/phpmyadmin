<?php

declare(strict_types=1);

namespace PhpMyAdmin\ConfigStorage;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Util;

use function sprintf;

/**
 * Set of functions used for cleaning up phpMyAdmin tables
 */
class RelationCleanup
{
    /** @var Relation */
    public $relation;

    /**
     * @param DatabaseInterface $dbi
     */
    public function __construct(public $dbi, Relation $relation)
    {
        $this->relation = $relation;
    }

    /**
     * Cleanup column related relation stuff
     *
     * @param string $db     database name
     * @param string $table  table name
     * @param string $column column name
     */
    public function column(string $db, string $table, string $column): void
    {
        $relationParameters = $this->relation->getRelationParameters();
        $columnCommentsFeature = $relationParameters->columnCommentsFeature;
        $displayFeature = $relationParameters->displayFeature;
        $relationFeature = $relationParameters->relationFeature;

        if ($columnCommentsFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s AND column_name = %s',
                Util::backquote($columnCommentsFeature->database),
                Util::backquote($columnCommentsFeature->columnInfo),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
                $this->dbi->quoteString($column),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($displayFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s AND display_field = %s',
                Util::backquote($displayFeature->database),
                Util::backquote($displayFeature->tableInfo),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
                $this->dbi->quoteString($column),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($relationFeature === null) {
            return;
        }

        $statement = sprintf(
            'DELETE FROM %s.%s WHERE master_db = %s AND master_table = %s AND master_field = %s',
            Util::backquote($relationFeature->database),
            Util::backquote($relationFeature->relation),
            $this->dbi->quoteString($db),
            $this->dbi->quoteString($table),
            $this->dbi->quoteString($column),
        );
        $this->dbi->queryAsControlUser($statement);

        $statement = sprintf(
            'DELETE FROM %s.%s WHERE foreign_db = %s AND foreign_table = %s AND foreign_field = %s',
            Util::backquote($relationFeature->database),
            Util::backquote($relationFeature->relation),
            $this->dbi->quoteString($db),
            $this->dbi->quoteString($table),
            $this->dbi->quoteString($column),
        );
        $this->dbi->queryAsControlUser($statement);
    }

    /**
     * Cleanup table related relation stuff
     *
     * @param string $db    database name
     * @param string $table table name
     */
    public function table(string $db, string $table): void
    {
        $relationParameters = $this->relation->getRelationParameters();
        $columnCommentsFeature = $relationParameters->columnCommentsFeature;
        $displayFeature = $relationParameters->displayFeature;
        $pdfFeature = $relationParameters->pdfFeature;
        $relationFeature = $relationParameters->relationFeature;
        $uiPreferencesFeature = $relationParameters->uiPreferencesFeature;
        $navigationItemsHidingFeature = $relationParameters->navigationItemsHidingFeature;

        if ($columnCommentsFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s',
                Util::backquote($columnCommentsFeature->database),
                Util::backquote($columnCommentsFeature->columnInfo),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($displayFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s',
                Util::backquote($displayFeature->database),
                Util::backquote($displayFeature->tableInfo),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($pdfFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s',
                Util::backquote($pdfFeature->database),
                Util::backquote($pdfFeature->tableCoords),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($relationFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE master_db = %s AND master_table = %s',
                Util::backquote($relationFeature->database),
                Util::backquote($relationFeature->relation),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);

            $statement = sprintf(
                'DELETE FROM %s.%s WHERE foreign_db = %s AND foreign_table = %s',
                Util::backquote($relationFeature->database),
                Util::backquote($relationFeature->relation),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($uiPreferencesFeature !== null) {
            $statement = sprintf(
                'DELETE FROM %s.%s WHERE db_name = %s AND table_name = %s',
                Util::backquote($uiPreferencesFeature->database),
                Util::backquote($uiPreferencesFeature->tableUiPrefs),
                $this->dbi->quoteString($db),
                $this->dbi->quoteString($table),
            );
            $this->dbi->queryAsControlUser($statement);
        }

        if ($navigationItemsHidingFeature === null) {
            return;
        }

        $statement = sprintf(
            'DELETE FROM %s.%s WHERE db_name = %s AND (table_name = %s OR (item_name = %s AND item_type = \'table\'))',
            Util::backquote($navigationItemsHidingFeature->database),
            Util::backquote($navigationItemsHidingFeature->navigationHiding),
            $this->dbi->quoteString($db),
            $this->dbi->quoteString($table),
            $this->dbi->quoteString($table),
        );
        $this->dbi->queryAsControlUser($statement);
    }

    /**
     * Cleanup database related relation stuff
     *
     * @param string $db database name
     */
    public function database($db): void
    {
        $relationParameters = $this->relation->getRelationParameters();
        if ($relationParameters->db === null) {
            return;
        }

        if ($relationParameters->columnCommentsFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->columnCommentsFeature->database)
                . '.' . Util::backquote($relationParameters->columnCommentsFeature->columnInfo)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->bookmarkFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->bookmarkFeature->database)
                . '.' . Util::backquote($relationParameters->bookmarkFeature->bookmark)
                . ' WHERE dbase  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->displayFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->displayFeature->database)
                . '.' . Util::backquote($relationParameters->displayFeature->tableInfo)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->pdfFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->pdfFeature->database)
                . '.' . Util::backquote($relationParameters->pdfFeature->pdfPages)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);

            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->pdfFeature->database)
                . '.' . Util::backquote($relationParameters->pdfFeature->tableCoords)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->relationFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->relationFeature->database)
                . '.' . Util::backquote($relationParameters->relationFeature->relation)
                . ' WHERE master_db  = \''
                . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);

            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->relationFeature->database)
                . '.' . Util::backquote($relationParameters->relationFeature->relation)
                . ' WHERE foreign_db  = \'' . $this->dbi->escapeString($db)
                . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->uiPreferencesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->uiPreferencesFeature->database)
                . '.' . Util::backquote($relationParameters->uiPreferencesFeature->tableUiPrefs)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->navigationItemsHidingFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->navigationItemsHidingFeature->database)
                . '.' . Util::backquote($relationParameters->navigationItemsHidingFeature->navigationHiding)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->savedQueryByExampleSearchesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->savedQueryByExampleSearchesFeature->database)
                . '.' . Util::backquote($relationParameters->savedQueryByExampleSearchesFeature->savedSearches)
                . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->centralColumnsFeature === null) {
            return;
        }

        $remove_query = 'DELETE FROM '
            . Util::backquote($relationParameters->centralColumnsFeature->database)
            . '.' . Util::backquote($relationParameters->centralColumnsFeature->centralColumns)
            . ' WHERE db_name  = \'' . $this->dbi->escapeString($db) . '\'';
        $this->dbi->queryAsControlUser($remove_query);
    }

    /**
     * Cleanup user related relation stuff
     *
     * @param string $username username
     */
    public function user($username): void
    {
        $relationParameters = $this->relation->getRelationParameters();
        if ($relationParameters->db === null) {
            return;
        }

        if ($relationParameters->bookmarkFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->bookmarkFeature->database)
                . '.' . Util::backquote($relationParameters->bookmarkFeature->bookmark)
                . " WHERE `user`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->sqlHistoryFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->sqlHistoryFeature->database)
                . '.' . Util::backquote($relationParameters->sqlHistoryFeature->history)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->recentlyUsedTablesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->recentlyUsedTablesFeature->database)
                . '.' . Util::backquote($relationParameters->recentlyUsedTablesFeature->recent)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->favoriteTablesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->favoriteTablesFeature->database)
                . '.' . Util::backquote($relationParameters->favoriteTablesFeature->favorite)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->uiPreferencesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->uiPreferencesFeature->database)
                . '.' . Util::backquote($relationParameters->uiPreferencesFeature->tableUiPrefs)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->userPreferencesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->userPreferencesFeature->database)
                . '.' . Util::backquote($relationParameters->userPreferencesFeature->userConfig)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->configurableMenusFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->configurableMenusFeature->database)
                . '.' . Util::backquote($relationParameters->configurableMenusFeature->users)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->navigationItemsHidingFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->navigationItemsHidingFeature->database)
                . '.' . Util::backquote($relationParameters->navigationItemsHidingFeature->navigationHiding)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->savedQueryByExampleSearchesFeature !== null) {
            $remove_query = 'DELETE FROM '
                . Util::backquote($relationParameters->savedQueryByExampleSearchesFeature->database)
                . '.' . Util::backquote($relationParameters->savedQueryByExampleSearchesFeature->savedSearches)
                . " WHERE `username`  = '" . $this->dbi->escapeString($username)
                . "'";
            $this->dbi->queryAsControlUser($remove_query);
        }

        if ($relationParameters->databaseDesignerSettingsFeature === null) {
            return;
        }

        $remove_query = 'DELETE FROM '
            . Util::backquote($relationParameters->databaseDesignerSettingsFeature->database)
            . '.' . Util::backquote($relationParameters->databaseDesignerSettingsFeature->designerSettings)
            . " WHERE `username`  = '" . $this->dbi->escapeString($username)
            . "'";
        $this->dbi->queryAsControlUser($remove_query);
    }
}
