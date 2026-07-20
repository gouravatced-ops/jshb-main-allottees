<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            ['document_name' => 'Aadhar Card', 'document_key' => 'aadhar', 'document_category' => 'Identity Proof', 'sort_order' => 1],
            ['document_name' => 'PAN Card', 'document_key' => 'pan', 'document_category' => 'Identity Proof', 'sort_order' => 2],
            ['document_name' => 'Driving License', 'document_key' => 'dl', 'document_category' => 'Identity Proof', 'sort_order' => 3],
            ['document_name' => 'Income Certificate', 'document_key' => 'income_cert', 'document_category' => 'Financial Document', 'sort_order' => 4],
            ['document_name' => 'Bank Passbook', 'document_key' => 'bank_passbook', 'document_category' => 'Financial Document', 'sort_order' => 5],
            ['document_name' => 'Caste Certificate', 'document_key' => 'caste_cert', 'document_category' => 'Other Document', 'sort_order' => 6],
            ['document_name' => 'Domicile Certificate', 'document_key' => 'domicile', 'document_category' => 'Other Document', 'sort_order' => 7],
        ];

        foreach ($documents as $doc) {
            \App\Models\DocumentMaster::updateOrCreate(
                ['document_key' => $doc['document_key']],
                $doc
            );
        }
    }
}
