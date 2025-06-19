<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompaniesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('companies')->delete();
        
        \DB::table('companies')->insert(array (
            0 => 
            array (
                'id' => '8fc9759e-1a54-4914-a9eb-94090a3a3f00',
                'name' => 'CNR',
                'piva' => '10282211001',
                'crm' => NULL,
                'callcenter' => NULL,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'created_at' => '2025-06-18 14:22:58',
                'updated_at' => '2025-06-18 14:22:58',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => '89183c99-5b8f-42cb-ae96-ef99a2f85ad0',
                'name' => 'ICAR',
                'piva' => NULL,
                'crm' => NULL,
                'callcenter' => NULL,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'created_at' => '2025-06-18 14:23:05',
                'updated_at' => '2025-06-18 14:23:05',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}