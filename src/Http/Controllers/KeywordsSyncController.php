<?php

namespace Yarm\AdminKeywords\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\Name;
use App\Models\Ref;
use Illuminate\Support\Facades\DB;

class KeywordsSyncController extends Controller
{
    public static function moveKeywordToTable()
    {
        //dd('start');
        $successful = [];
        $refs=Ref::where('keywords','!=','')->where('keywords','!=','primary')->where('keywords','!=','secondary')->get();
        $i=0;
        foreach ($refs as $ref) {
            //take ref
            if (isset($ref)) {
                try {
                    //->get keywords
                    //explode en loop
                    //make keywords array
                    $keywords = explode(';', trim($ref->keywords));
                    if (count($keywords) > 0) {
                        foreach ($keywords as $k) {
                            $k = trim($k);
                            if ($k != '') {
                                $keyword = null;
                                //if primary or secondary? ignore
                                if (strtolower($k) != "primary" && strtolower($k) != "secondary") {
                                    //remove primaries with ,
                                    $k=trim(self::removeUnwanted($k));
                                    //save them via checks or get them
                                    //search in translation
                                    $presentInKeywords = self::searchInKeywordTranslation($k);
                                    if ($presentInKeywords !== null) {
                                        //if present take that keyword
                                        $keyword = $presentInKeywords;
                                    } else {
                                        //else look in names to see if it matches any
                                        $presentInNames = self::findKeywordInNames($k);
                                        if ($presentInNames->count() === 1) {
                                            //if it matches a name? check if the name exist as a keyword
                                            $kywd = Keyword::where('name_id', '=', $presentInNames[0]->id)->first();
                                            if ($kywd !== null) {
                                                //if its a name-keyword?  take it
                                                $keyword = $kywd;
                                            }
                                        }
                                    }
                                    //if $keyword is still null? its a new keyword
                                    if ($keyword === null) {
                                        //make and save new keyword
                                        $new_keyword = new Keyword();
                                        $new_keyword->name = $k;
                                        [$keyword_save, $saved_keyword] = self::saveKeywordWithChecks($new_keyword);
                                        if ($keyword_save != true) return dd('keyword save error : ' . $new_keyword->name);
                                        $keyword = $saved_keyword;
                                    }
                                    //link to ref
                                    $keyword->refs()->syncWithoutDetaching($ref->id);
                                }
                            }
                        }
                    }
                    //add ref to successfull
                    $i++;
                    $successful[] = $ref->id;
                    echo("record successfully synced : " . $ref->id . "->" . ((Keyword::getKeywordsString($ref->id) == "") ? " -------- no keywords" : (Keyword::getKeywordsString($ref->id))) . "  <br>");
                } catch (\Throwable $e) {
                    echo("Error: " . $e . "with: " . $i);
                    //dd($e);
                    exit;
                }
            }

        }
        echo("records successfully synced : " . count($successful) . " <br>");
    }

    private static function removeUnwanted($keyword)
    {
        $unwanted = ["primary,", "primary ,", ", primary",",primary"];
        return str_replace($unwanted, "", $keyword);
    }

    public static function saveKeywordWithChecks(Keyword $keyword){
        try {
            $result =self::findKeywordInNames($keyword->name);
            if ($result->count() === 1) {
                $keyword->name = $result[0]->name . ' ' . $result[0]->first_name;
                $keyword->name_id = $result[0]->id;
            }
            $keyword->translation = ((trim($keyword->translation) == '') ? '' : $keyword->translation . ';') . $keyword->name;
            $success= [$keyword->save(),$keyword];
            return $success;
        }   catch (\Throwable $e) {
            return [response()->json([
                'errors' =>[$e->getMessage()]]),null];
        }
    }
    public static function findKeywordInNames($name){
        $query = Name::select('names.name', 'names.first_name', 'names.alternative_names', 'names.id');
        $query->where(function ($query) use ($name) {
            $query->where(DB::raw('CONCAT(names.name, " ", names.first_name)'), '=', $name);
            $query->orwhere(DB::raw('CONCAT(names.first_name, " ", names.name)'), '=', $name);
            $query->orwhere('names.alternative_names', 'LIKE', '%;' . $name . ';%');
            $query->orwhere('names.alternative_names', 'LIKE', '%;' . $name);
            $query->orwhere('names.alternative_names', 'LIKE', $name . ';%');
            $query->orwhere('names.alternative_names', '=', $name);
        });
        return $query->get();

    }

    public static function searchInKeywordTranslation($name){
        $query= Keyword::select();
        $query->where(function ($query) use ($name) {
            $query->where('translation', '=', $name);
            $query->orwhere('translation', 'like', $name . ";%");
            $query->orwhere('translation', 'like', "%;" . $name);
            $query->orwhere('translation', 'like', "%;" . $name . ";%");
        });
        return $query->first();
    }

}
