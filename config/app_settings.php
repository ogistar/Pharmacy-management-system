<?php

return [

    // All the sections for the settings page
    'sections' => [
        'app' => [
            'title' => 'General Settings',
            'descriptions' => '', // (optional)
            'icon' => 'fa fa-cog', // (optional)

            'inputs' => [
                [
                    'name' => 'app_name', // unique key for setting
                    'type' => 'text', // type of input can be text, number, textarea, select, boolean, checkbox etc.
                    'label' => 'App Name', // label for input
                    // optional properties
                    'placeholder' => 'Application Name', // placeholder for input
                    'class' => 'form-control', // override global input_class
                    'style' => '', // any inline styles
                    'rules' => 'required|min:2|max:20', // validation rules for this input
                    'value' => config('app.name'), // any default value
                    'hint' => 'You can set the app name here' // help block text for input
                ],
                [
                    'name' => 'app_currency_code',
                    'type' => 'select',
                    'label' => 'Currency',
                    'options' => [
                        'IDR' => 'IDR (Rupiah)',
                        'USD' => 'USD (US Dollar)',
                        'SGD' => 'SGD (Singapore Dollar)',
                        'MYR' => 'MYR (Malaysia Ringgit)',
                        'JPY' => 'JPY (Japan Yen)',
                        'EUR' => 'EUR (Euro)',
                    ],
                    'value' => 'IDR',
                    'hint' => 'Pilih mata uang utama aplikasi',
                ],
                [
                    'name' => 'app_currency_symbol',
                    'type' => 'select',
                    'label' => 'Currency Symbol',
                    'options' => [
                        'Rp' => 'Rp (Rupiah)',
                        '$' => '$ (Dollar)',
                        'S$' => 'S$ (Singapore Dollar)',
                        'RM' => 'RM (Ringgit)',
                        '¥' => '¥ (Yen)',
                        '€' => '€ (Euro)',
                    ],
                    'value' => 'Rp',
                    'hint' => 'Simbol yang dipakai di tampilan',
                ],
                [
                    'name' => 'app_currency_thousand',
                    'type' => 'select',
                    'label' => 'Thousand Separator',
                    'options' => [
                        ',' => '1,000',
                        '.' => '1.000',
                        ' ' => '1 000',
                    ],
                    'value' => '.',
                ],
                [
                    'name' => 'app_currency_decimal',
                    'type' => 'select',
                    'label' => 'Decimal Separator',
                    'options' => [
                        '.' => '0.00',
                        ',' => '0,00',
                    ],
                    'value' => ',',
                ],
                [
                    'name' => 'logo',
                    'type' => 'image',
                    'label' => 'Upload logo',
                    'hint' => 'Recommended image size is 150px x 150px',
                    'rules' => 'image|max:500',
                    'disk' => 'public', // which disk you want to upload
                    'path' => 'logos', // path on the disk,
                    'preview_class' => 'thumbnail',
                    'preview_style' => 'height:40px'
                ]
                   ,
                [
                    'name' => 'favicon',
                    'type' => 'image',
                    'label' => 'Upload favicon',
                    'hint' => 'Recommended image size is 16px x 16px or 32px x 32px',
                    'rules' => 'image|max:500',
                    'disk' => 'public', // which disk you want to upload
                    'path' => 'logos', // path on the disk,
                    'preview_class' => 'thumbnail',
                    'preview_style' => 'height:40px'
                ],
            ]
        ],
        'printer' => [
            'title' => 'Thermal Printer',
            'icon' => 'fa fa-print',
            'inputs' => [
                [
                    'name' => 'thermal_enabled',
                    'type' => 'boolean',
                    'label' => 'Enable thermal printing',
                    'hint' => 'Aktifkan jika ingin cetak struk thermal',
                    'value' => false,
                ],
                [
                    'name' => 'thermal_printer_name',
                    'type' => 'text',
                    'label' => 'Device name/IP',
                    'placeholder' => 'Contoh: EPSON-TM-T82 / 192.168.1.50',
                    'rules' => 'nullable|string|max:100',
                ],
                [
                    'name' => 'thermal_paper_width',
                    'type' => 'text',
                    'label' => 'Paper width',
                    'placeholder' => '80mm atau 58mm',
                    'rules' => 'nullable|string|max:20',
                    'value' => '80mm',
                ],
            ],
        ],
        
    ],

    // Setting page url, will be used for get and post request
    'url' => 'settings',

    // Any middleware you want to run on above route
    'middleware' => ['auth'],

    // View settings
    // 'setting_page_view' => 'app_settings::settings_page',
    'setting_page_view' => 'admin.settings',
    'flash_partial' => 'app_settings::_flash',

    // Setting section class setting
    'section_class' => 'card mb-3',
    'section_heading_class' => 'card-header',
    'section_body_class' => 'card-body',

    // Input wrapper and group class setting
    'input_wrapper_class' => 'form-group',
    'input_class' => 'form-control',
    'input_error_class' => 'has-error',
    'input_invalid_class' => 'is-invalid',
    'input_hint_class' => 'form-text text-muted',
    'input_error_feedback_class' => 'text-danger',

    // Submit button
    'submit_btn_text' => 'Save Settings',
    'submit_success_message' => 'Settings has been saved.',

    // Remove any setting which declaration removed later from sections
    'remove_abandoned_settings' => false,

    // Controller to show and handle save setting
    'controller' => '\App\Http\Controllers\Admin\SettingController',

    // settings group
    'setting_group' => function() {
        // return 'user_'.auth()->id();
        return 'default';
    }
];
