<?php

namespace App\Http\Controllers;

/**
 * Base Controller untuk semua controller di aplikasi
 * 
 * Ini adalah class abstract (tidak bisa diinstansiasi langsung)
 * Semua controller lain akan extends (mewarisi) class ini
 * 
 * Saat ini masih kosong, tapi biasanya di sini ditaruh:
 * - Middleware yang dipakai semua controller
 * - Method helper yang sering dipakai
 * - Auth logic
 */
abstract class Controller
{
    //
}