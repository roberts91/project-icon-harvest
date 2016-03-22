<?php

namespace App\Http\Controllers;


use Mail;
use Config;
use App\Icon;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

class IconCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // Get icons
        $icons = (object) [
            'fa'        => Icon::where('type', 'fa')->get(),
            'gmi'       => Icon::where('type', 'gmi')->get(),
            '7-stroke'  => Icon::where('type', '7-stroke')->get(),
            'dashicons' => Icon::where('type', 'wp')->get()
        ];
        
        // Count all icons
        $icon_count = Icon::where('type', 'fa')->orWhere('type', 'gmi')->orWhere('type', '7-stroke')->orWhere('type', 'wp')->count();
        
        // Get all icons files
        $icon_files = Config::get('icons.files', []);

        // Display view
        return view('icon-check', compact('icons', 'icon_files', 'icon_count'));
        
    }
}