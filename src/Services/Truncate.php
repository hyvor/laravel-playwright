<?php declare(strict_types=1);

namespace Hyvor\LaravelPlaywright\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Truncate
{

    /**
     * @param array<null | string> $connections
     * @param string[]|null $except Tables to keep (truncate everything else)
     */
    public function truncate(array $connections = [null], ?array $except = null) : void
    {

        foreach ($connections as $connection) {
            $this->truncateTablesOfConnection($connection, $except);
        }

    }

    /**
     * @param string[]|null $except
     */
    private function truncateTablesOfConnection(?string $connection, ?array $except) : void
    {

        /** @var string[] $tables */
        $tables = Schema::connection($connection)->getTableListing();

        if ($except !== null) {
            $tables = array_values(array_diff($tables, $except));
        }

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

    }

}
