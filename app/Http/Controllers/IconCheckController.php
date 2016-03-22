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
            '7-stroke'  => Icon::where('type', '7-stroke')->get()
        ];
        
        // Get all icons files
        $icon_files = Config::get('icons.files', []);

        // Display view
        return view('icon-check', compact('icons', 'icon_files'));
        
    }
}