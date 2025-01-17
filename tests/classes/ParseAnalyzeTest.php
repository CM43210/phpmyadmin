<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\ParseAnalyze;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\StatementInfo;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParseAnalyze::class)]
class ParseAnalyzeTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DatabaseInterface::$instance = $this->createDatabaseInterface();
    }

    public function testSqlQuery(): void
    {
        $GLOBALS['lang'] = 'en';
        ResponseRenderer::getInstance()->setAjax(false);

        $GLOBALS['unparsed_sql'] = '';

        $actual = ParseAnalyze::sqlQuery('SELECT * FROM `sakila`.`actor`', 'sakila_test');

        /** @psalm-suppress TypeDoesNotContainType */
        self::assertSame('SELECT * FROM `sakila`.`actor`', $GLOBALS['unparsed_sql']);
        self::assertCount(3, $actual);
        self::assertInstanceOf(StatementInfo::class, $actual[0]);
        self::assertSame('sakila', $actual[1]);
        self::assertSame('actor', $actual[2]);
        self::assertTrue($actual[0]->reload);
        self::assertNotEmpty($actual[0]->selectTables);
        self::assertSame([['actor', 'sakila']], $actual[0]->selectTables);
        self::assertNotEmpty($actual[0]->selectExpression);
        self::assertSame(['*'], $actual[0]->selectExpression);
    }

    public function testSqlQuery2(): void
    {
        $GLOBALS['lang'] = 'en';
        ResponseRenderer::getInstance()->setAjax(false);

        $GLOBALS['unparsed_sql'] = '';

        $actual = ParseAnalyze::sqlQuery('SELECT `first_name`, `title` FROM `actor`, `film`', 'sakila');

        /** @psalm-suppress TypeDoesNotContainType */
        self::assertSame('SELECT `first_name`, `title` FROM `actor`, `film`', $GLOBALS['unparsed_sql']);
        self::assertCount(3, $actual);
        self::assertInstanceOf(StatementInfo::class, $actual[0]);
        self::assertSame('sakila', $actual[1]);
        self::assertSame('', $actual[2]);
        self::assertFalse($actual[0]->reload);
        self::assertNotEmpty($actual[0]->selectTables);
        self::assertSame([['actor', null], ['film', null]], $actual[0]->selectTables);
        self::assertNotEmpty($actual[0]->selectExpression);
        self::assertSame(['`first_name`', '`title`'], $actual[0]->selectExpression);
    }
}
