<?php

namespace Yarm\Adminkeywords\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaginationController;
use App\Http\Controllers\ValidationController;
use App\Models\Keyword;
use Illuminate\Http\Request;

class KeywordAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:admin');
    }

    public function index()
    {
        $paginationValue = PaginationController::getPaginationItemCount();

        $data['keywords'] = Keyword::orderBy('name', 'asc')->paginate($paginationValue);
        return view('adminkeywords::table', $data);

    }

    public function edit($id)
    {
        if (str_contains($id, '&p')) [$id, $current_page] = explode('&p', $id);
        $keyword = Keyword::find($id);
        if ($id != 0) $data['redirect'] = url()->previous() . '?page=' . ($current_page ?? 1);
        $data['keyword_to_clean'] = $keyword;
        return view('adminkeywords::edit', $data);

    }

    public function update(Request $request)
    {

        //        todo lang
        $validation = $this->keywordCleanerValidation($request);
        if ($validation !== 200) return $validation;

        //if split
        if (isset($request['split-keyword']) && (trim($request['split-keyword']) === "yes")) {
            $split = $this->splitKeyword($request);
            if ($split !== 200) return $split;
        }
        //if change to selected
        if (isset($request['split-keyword']) && (trim($request['split-keyword']) === "no")) {
            $toSelected = $this->changeToSelectedKeyword($request);
            if ($toSelected !== 200) return $toSelected;
        }

        if ((isset($request['redirect']) && (trim($request['redirect']) != ""))) {
            return redirect($request->redirect)
                ->with('alert-success', __('Changes successfully saved'));
        }

        return redirect('/' . strtolower(config('yarm.sys_name')) . '/keywords/edit/0')
            ->with('alert-success', __('Changes successfully saved'));
    }

    private static function keywordCleanerValidation($request)
    {
//        todo lang
        //Check valid request to prevent XSS!
        list($isValid, $error_messages) = ValidationController::validateRequest($request);
        //Return back with error message
        if ($isValid == false) {
            return redirect()->back()->withInput()
                ->with('alert-danger', $error_messages);
        }
        //check for keyword count (1),
        if (!(isset($request->id) && count($request->id) > 0)) {
            return redirect()->back()->withInput()->with('alert-danger', "At least one keyword should be present!");
        }
        //id[] shouldnt have null
        if (in_array(null, $request->id)) {
            return redirect()->back()->withInput()->with('alert-danger', "Please make sure every item is a keyword from the list or a newly created keyword!");
        }
        //all good
        $success = 200;
        return $success;
    }

    public function keywordFetch_Data (Request $request)
    {
        $paginationValue = PaginationController::getPaginationItemCount();
        if ($request->ajax()) {
            $q = $request->get('query');
            $q = str_replace(" ", "%", $q);
            $modelName = strtolower($request->get('type'));
            if ($modelName == 'keywords_crud') {
                $data['keywords'] = Keyword::where('translation', 'like', '%' . $q . '%')
                    ->orderBy('name', 'asc')->paginate($paginationValue);
                return view('adminkeywords::inc.table_data', $data);
            }
        }
    }



    private static function splitKeyword($request)
    {
//        todo lang
        $validationSplit = self::keywordSplitValidation($request);
        if ($validationSplit !== 200) return $validationSplit;

        $keywords = explode(';', trim($request->name[0]));
        $keywords = array_diff($keywords, ['']);

        //check the position of the ;
        if (!(count($keywords) > 1)) return redirect()->back()->withInput()
            ->with('alert-danger', "Please check the position of the ';', it should be between the 2 keywords you want to split");

        //get original keyword
        $ori_keyword = Keyword::find($request->id[0]);
        $ori_keyword_ref_ids = $ori_keyword->refs()->pluck('refs.id')->toArray();
        foreach ($keywords as $k) {
            $keyword = new Keyword();
            $keyword->name = trim($k);
            [$save, $saved_keyword] = KeywordController::saveKeywordWithChecks($keyword);
            if ($save !== true) return $save;
            //attach refs
            if (count($ori_keyword_ref_ids) > 0) $saved_keyword->refs()->attach($ori_keyword_ref_ids);
        }
        //delete original keyword;
        $ori_keyword->delete();

        $success = 200;
        return $success;
    }

    private static function keywordSplitValidation($request)
    {
//        todo lang
        //->id present
        if (count($request->id) > 1) return redirect()->back()->withInput()
            ->with('alert-danger', "Please make sure there is only one keyword present, if you want to split!");

        //->check if contains ;
        if (!(str_contains($request->name[0], ';'))) return redirect()->back()->withInput()
            ->with('alert-danger', "Please use ';' to separate the keywords you want to split!");

        //all good
        $success = 200;
        return $success;
    }




    private static function changeToSelectedKeyword($request)
    {
//        todo lang
        try {
            $validationChange = self::keywordChangeValidation($request);
            if ($validationChange !== 200) return $validationChange;

            $selected_keyword_id = $request->selected_keyword;
            //strip all the rests clients and trans
            $ref_ids = [];
            $translations = [];
            foreach ($request->id as $k_id) {
                $keyword = Keyword::find($k_id);
                $ref_ids = array_merge($ref_ids, $keyword->refs()->pluck('refs.id')->toArray());
                $translations = array_merge($translations, explode(';', trim($keyword->translation)));
                //delete other keywords !! after the above reading is finished
                if ($keyword->id != $request->id[$selected_keyword_id]) $keyword->delete();
            }
            //set selected keyword
            $s_keyword = Keyword::find($request->id[$selected_keyword_id]);
            $s_keyword->refs()->attach(array_diff($ref_ids, $s_keyword->refs()->pluck('refs.id')->toArray()));
            $s_keyword->translation = implode(';', array_unique($translations));
            $s_keyword->save();
            $success = 200;
            return $success;
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('alert-danger', $e->getMessage());
        }
    }

    private static function keywordChangeValidation($request)
    {
//        todo lang
        //selected is present
        if (!(isset($request->selected_keyword))) return redirect()->back()->withInput()
            ->with('alert-danger', "Please make a selection of the keyword you want to change to!");
        //there shoulb be atleast 2 present
        if ((isset($request->id)) && count($request->id) <= 1) return redirect()->back()->withInput()
            ->with('alert-danger', "There should be at least 2 keyword fields!");

        //all good
        $success = 200;
        return $success;
    }






    public function destroy($id, Request $request)
    {
        try {
            $page = (isset($request->page)) ? ($request->page) : 1;
            $keyword = Keyword::find($id);
            $keyword->refs()->detach();
            $keyword->delete();
            session()->flash('success', "The keyword <b>$keyword->name</b> has been deleted!");
            return redirect(strtolower(config('yarm.sys_name')) .'/keywords?page=' . $page);
        } catch (\Throwable $e) {
            return back()->with('alert-danger', $e->getMessage());
        }
    }



    public function editAjax($id)
    {
        $keyword = Keyword::find($id);
        return response()->json([
            'data' => $keyword
        ]);
    }

    public function updateAjax(Request $request, $id)
    {
        $keyword = Keyword::find($id);
        $keyword->name = $request->name;
        $keyword->translation = $request->translation;
        $keyword->save();
        return response()->json(['success' => true]);

    }
}
