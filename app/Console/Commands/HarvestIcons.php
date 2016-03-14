<?php

namespace App\Console\Commands;

use Log;
use App\Icon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Yaml\Parser;

class HarvestIcons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvesticons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harvest icons from Font Awesome and Google Material Design and push them to Algolia.';

    /**
     * Class constrcutor
     */
    function __construct()
    {
        parent::__construct();
        
        // Get file urls
        $this->files = $this->get_files();
    }
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log
        Log::info('Starting IconHarvest');
        
        // Google Material Design Icons
        $material_icons = $this->fetch($this->files->google);
        $mi_formatted = $this->formatResult($material_icons, 'material_icons');
        
        // Font Awesome
        $font_awesome   = $this->fetch($this->files->fa, 'yaml');
        $fa_formatted = $this->formatResult($font_awesome, 'fa');
        
        // Merge icons
        $all_icons = array_merge($mi_formatted, $fa_formatted);
        
        // Loop through all icons
        foreach($all_icons as $i)
        {
            
            // Define new icon
            $icon = new Icon;
            
            // Check if exists
            $icon = $icon->where('icon_id', '=', $i['icon_id'])->first();
            
            // Check if exists
            if ($icon === null)
            {
                // Define new icon
                $icon = new Icon;
                
                // Insert
                $icon->firstOrCreate($i);
            }
            else
            {
                // Get row
                $icon_check = $icon->where('icon_id', '=', $i['icon_id'])->get()->first()->toArray();
                
                // Unset Laravel-specific fields
                unset($icon_check['id']);
                unset($icon_check['created_at']);
                unset($icon_check['updated_at']);
                
                // Calculate diffs of both key and value
                $diff = array_diff_assoc($icon_check, $i);
                
                // Check if we got diff
                if(is_array($diff) AND count($diff) > 0)
                {
                    // Update
                    $icon->fill($i);
                    $icon->save();
                }
            }
        }
        
        // Log
        Log::info('Stopping IconHarvest');
    }
    
    /**
     * Get array of files containing icons
     *
     * @return object
     */
    private function get_files()
    {
        return (object) [
            'google' => 'https://raw.githubusercontent.com/google/material-design-icons/master/iconfont/MaterialIcons-Regular.ijmap',
            'fa'     => 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/src/icons.yml'
        ];
    }
    
    /**
     * Remove blacklisten words from array of words
     *
     * @return array
     */
    private function removeBlacklistedWords($categories, $blacklist)
    {
        // Abort if no categories
        if(!is_array($categories))
        {
            return false;
        }
        
        // Concatenate and explode array into words
        $category_words = explode(' ', mb_strtolower(implode(' ', $categories)));
        
        // Define var
        $passed = [];
        
        // Loop through words
        foreach($category_words as $word)
        {
            // Add to passed if not in blacklist
            if(!in_array($word, $blacklist))
            {
                // Add to passed-array
                $passed[] = $word;
            }
        }
        
        // Return passed words
        return $passed;
    }

    /**
     * Format icon-result based on which provder
     *
     * @return array
     */
    private function formatResult($content, $type)
    {
        
        // Create resultarray
        $formatted = [];
        
        // Determine provider
        switch($type)
        {
            case 'material_icons':
                
                // Get icons
                $icons = $content->icons;
                
                // Loop through icons
                foreach($icons as $key => $value)
                {
                    
                    // Get name
                    $name = $value->name;
                    
                    // Build ID
                    $id = 'gmi-' . $key;
                    
                    // Build array
                    $formatted[] = array(
                        'icon_id' => $id,
                        'code'    => $key,
                        'type'    => 'gmi',
                        'name'    => $name,
                        'tags'    => null
                    );
                }
                
            break;
            case 'fa':
            
                // Get icons
                $icons = $content['icons'];
                
                // Loop through icons
                foreach($icons as $icon)
                {
                    // Get filter
                    $filter = (isset($icon['filter']) ? $icon['filter'] : []);
                    
                    // Get aliases
                    $aliases = (isset($icon['aliases']) ? $icon['aliases'] : []);
                    
                    // Add fa-{code} to aliases
                    $aliases[] = 'fa-' . $icon['id'];
                        
                    // Get categories
                    $categories = (isset($icon['categories']) ? $icon['categories'] : []);
                    
                    // Define blacklisted words
                    $blacklist = array('web','application', 'icon', 'icons');
                    
                    // Filter out blacklisted words
                    $categories = $this->removeBlacklistedWords($categories, $blacklist);
                    
                    // Merge words
                    $tags = array_merge($filter, $categories, $aliases);
                    
                    // Remove duplicates
                    $tags = array_unique($tags);
                    
                    // Build ID
                    $id = 'fa-' . $icon['unicode'];
                    
                    // Build array
                    $formatted[] = array(
                        'icon_id' => $id,
                        'code'    => $icon['id'],
                        'type'    => 'fa',
                        'name'    => $icon['name'],
                        'tags'    => implode(' ', $tags)
                    );
                }
                
            break;
            default:
                // Invalid provider
                return false;
        }

        // Return result
        return $formatted;
    }

    /**
     * Fetch content of file and decode in either JSON og YAML
     *
     * @return array/object
     */
    private function fetch($url, $type = 'json')
    {
        
        // Fetch content
        $content = file_get_contents($url);
        
        // Check if result is empty
        if(empty($content))
        {
            // TODO: Make trigger to tell me somthing is wrong
        }
        
        // Detemine resulttype
        switch($type)
        {
            case 'json':
            
                // Try decoding
                if(!$result = json_decode($content))
                {
                    // Failed
                    return false;
                }
                
                // Return data
                return $result;
                
            break;
            case 'yaml':
            
                // Defining new parser
                $yaml = new Parser();
                
                // Try to parse
                if(!$result = $yaml->parse($content))
                {
                    // Failed
                    return false;
                }
                
                // Return data
                return $result;
                
            break;
        }
        return false;
    }

}
