<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Laravel only ships English messages, so the rules
    | that this application actually uses are translated here. Anything that is
    | missing falls back to the English file through `fallback_locale`.
    |
    */

    'accepted' => 'Kolom :attribute harus disetujui.',
    'active_url' => 'Kolom :attribute bukan URL yang valid.',
    'after' => 'Kolom :attribute harus berisi tanggal setelah :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Kolom :attribute harus berupa array.',
    'before' => 'Kolom :attribute harus berisi tanggal sebelum :date.',
    'between' => [
        'array' => 'Kolom :attribute harus berisi antara :min sampai :max item.',
        'file' => 'Kolom :attribute harus berukuran antara :min sampai :max kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai antara :min sampai :max.',
        'string' => 'Kolom :attribute harus berisi antara :min sampai :max karakter.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'date_equals' => 'Kolom :attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => 'Kolom :attribute tidak cocok dengan format :format.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits digit.',
    'digits_between' => 'Kolom :attribute harus terdiri dari :min sampai :max digit.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'ends_with' => 'Kolom :attribute harus diakhiri dengan salah satu dari: :values.',
    'exists' => 'Kolom :attribute yang dipilih tidak valid.',
    'file' => 'Kolom :attribute harus berupa berkas.',
    'filled' => 'Kolom :attribute wajib diisi.',
    'gt' => [
        'array' => 'Kolom :attribute harus berisi lebih dari :value item.',
        'file' => 'Kolom :attribute harus lebih besar dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih besar dari :value.',
        'string' => 'Kolom :attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Kolom :attribute harus berisi :value item atau lebih.',
        'file' => 'Kolom :attribute harus lebih besar atau sama dengan :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih besar atau sama dengan :value.',
        'string' => 'Kolom :attribute harus :value karakter atau lebih.',
    ],
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Kolom :attribute yang dipilih tidak valid.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Kolom :attribute harus berupa teks JSON yang valid.',
    'lt' => [
        'array' => 'Kolom :attribute harus berisi kurang dari :value item.',
        'file' => 'Kolom :attribute harus lebih kecil dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih kecil dari :value.',
        'string' => 'Kolom :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :value item.',
        'file' => 'Kolom :attribute harus lebih kecil atau sama dengan :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih kecil atau sama dengan :value.',
        'string' => 'Kolom :attribute harus :value karakter atau kurang.',
    ],
    'max' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :max item.',
        'file' => 'Kolom :attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => 'Kolom :attribute tidak boleh lebih besar dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => 'Kolom :attribute harus berupa berkas dengan tipe: :values.',
    'mimetypes' => 'Kolom :attribute harus berupa berkas dengan tipe: :values.',
    'min' => [
        'array' => 'Kolom :attribute harus berisi minimal :min item.',
        'file' => 'Kolom :attribute harus berukuran minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai minimal :min.',
        'string' => 'Kolom :attribute harus berisi minimal :min karakter.',
    ],
    'not_in' => 'Kolom :attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'present' => 'Kolom :attribute harus ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_if' => 'Kolom :attribute wajib diisi bila :other bernilai :value.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other bernilai :values.',
    'required_with' => 'Kolom :attribute wajib diisi bila :values ada.',
    'required_with_all' => 'Kolom :attribute wajib diisi bila :values ada.',
    'required_without' => 'Kolom :attribute wajib diisi bila :values tidak ada.',
    'required_without_all' => 'Kolom :attribute wajib diisi bila tidak ada satu pun dari :values.',
    'same' => 'Kolom :attribute dan :other harus sama.',
    'size' => [
        'array' => 'Kolom :attribute harus berisi :size item.',
        'file' => 'Kolom :attribute harus berukuran :size kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus berisi :size karakter.',
    ],
    'starts_with' => 'Kolom :attribute harus diawali dengan salah satu dari: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => 'Kolom :attribute sudah digunakan.',
    'uploaded' => 'Kolom :attribute gagal diunggah.',
    'url' => 'Kolom :attribute harus berupa URL yang valid.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Following lines are used to swap the field name in the message above with
    | something more readable, such as "email" instead of "alamat email".
    |
    */

    'attributes' => [
        'benefits' => 'keunggulan',
        'background_image' => 'gambar latar',
        'button_text' => 'teks tombol',
        'button_url' => 'tautan tombol',
        'cta_primary_text' => 'teks tombol utama',
        'cta_primary_url' => 'tautan tombol utama',
        'cta_secondary_text' => 'teks tombol kedua',
        'cta_secondary_url' => 'tautan tombol kedua',
        'description' => 'deskripsi',
        'disclaimer' => 'keterangan tambahan',
        'email' => 'email',
        'icon' => 'ikon',
        'image' => 'gambar',
        'link' => 'tautan',
        'logo' => 'logo',
        'logo_adaptive' => 'warna logo adaptif',
        'name' => 'nama',
        'order_column' => 'urutan',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'site_name' => 'nama situs',
        'subtitle' => 'subjudul',
        'title' => 'judul',
    ],

];
