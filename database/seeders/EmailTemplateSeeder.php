<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplate::updateOrCreate(
            ['type' => 'customer'],
            [
                'from_email' => 'noreply@u9.com.mm',
                'to_email' => ['hnandar.extl@u9.com.mm'],
                'subject' => 'New Customer Inquiry',
                'body_header' => '<h2>New Customer Inquiry</h2>',
                'body_footer' => '<p>Thank you.</p>',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['type' => 'partner'],
            [
                'from_email' => 'noreply@u9.com.mm',
                'to_email' => ['hnandar.extl@u9.com.mm'],
                'subject' => 'New Partner Inquiry',
                'body_header' => '<h2>New Partner Inquiry</h2>',
                'body_footer' => '<p>Thank you.</p>',
            ]
        );
    }
}
