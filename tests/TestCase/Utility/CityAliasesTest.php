<?php
declare(strict_types=1);

namespace App\Test\TestCase\Utility;

use App\Utility\CityAliases;
use Cake\TestSuite\TestCase;

/**
 * CityAliasesTest
 *
 * Guards the search city filter: typos/short codes must resolve,
 * distinct cities must never leak into each other.
 */
class CityAliasesTest extends TestCase
{
    public function testCanonicalizeAliases(): void
    {
        $this->assertSame('arusha', CityAliases::canonicalize('arusa'));
        $this->assertSame('arusha', CityAliases::canonicalize('Arusha Town'));
        $this->assertSame('daressalaam', CityAliases::canonicalize('dsm'));
        $this->assertSame('daressalaam', CityAliases::canonicalize('Dar es Salam'));
        $this->assertSame('daressalaam', CityAliases::canonicalize('Dar es Salaam'));
        $this->assertSame('zanzibar', CityAliases::canonicalize('znz'));
        $this->assertSame('zanzibar', CityAliases::canonicalize('Stone Town'));
        $this->assertSame('kilimanjaro', CityAliases::canonicalize('moshi'));
        $this->assertSame('serengeti', CityAliases::canonicalize('seronera'));
    }

    public function testCanonicalizeUnknownPassesThrough(): void
    {
        $this->assertSame('nonexistentxyz123', CityAliases::canonicalize('NonExistentXYZ123'));
    }

    public function testMatchesExactAndAliases(): void
    {
        $this->assertTrue(CityAliases::matches('Arusha', 'Arusha'));
        $this->assertTrue(CityAliases::matches('arusa', 'Arusha'));
        $this->assertTrue(CityAliases::matches('dsm', 'Dar es Salaam'));
        $this->assertTrue(CityAliases::matches('Dar es Salam', 'Dar es Salaam'));
        $this->assertTrue(CityAliases::matches('moshi', 'Kilimanjaro'));
        $this->assertTrue(CityAliases::matches('Paje', 'Zanzibar', 'Paje'));
    }

    public function testMatchesNeverLeaksAcrossCities(): void
    {
        $this->assertFalse(CityAliases::matches('Arusha', 'Dar es Salaam'));
        $this->assertFalse(CityAliases::matches('Arusha', 'Zanzibar'));
        $this->assertFalse(CityAliases::matches('dsm', 'Arusha'));
        $this->assertFalse(CityAliases::matches('Zanzibar', 'Arusha'));
        $this->assertFalse(CityAliases::matches('moshi', 'Arusha'));
    }
}
