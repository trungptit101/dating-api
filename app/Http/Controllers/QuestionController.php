<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//import the model
use App\Models\Question;
use App\Models\QuestionnaireUser;
use App\Models\FilterSurvey;

//import the Validator
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Auth;

class QuestionController extends Controller
{
    public function listQuestion(Request $request)
    {
        $questions = Question::query()
            ->orderBy("id", "asc")
            ->paginate($request->input("perPage"), ["*"], "page", $request->input("page"));;
        return response()->json([
            "data" => $questions,
            "code" => 200
        ], 200);
    }

    public function getSettingsFilter(Request $request)
    {
        $settings = FilterSurvey::query()->first();
        return response()->json([
            "data" => $settings,
            "code" => 200
        ], 200);
    }

    public function getQuestionSettingsFilter(Request $request)
    {
        $settings = FilterSurvey::query()->first();
        if (isset($settings)) {
            $questionIds = json_decode($settings->questions_id);
            $questions = Question::query()->whereIn("id", $questionIds)->get();
        } else {
            $questions = [];
        }
        $questions = collect($questions)->map(function ($question) {
            $question->options = json_decode($question->options);
            return $question;
        })->values();
        return response()->json([
            "questions" => $questions,
            "code" => 200
        ], 200);
    }

    public function upSertSettingsFilter(Request $request)
    {
        if ($request->input("id")) {
            $settings = FilterSurvey::find($request->input("id"));
            $settings->questions_id = json_encode($request->input("questionIds"));
            $settings->save();
        } else {
            $settings = FilterSurvey::create([
                "questions_id" => json_encode($request->input("questionIds")),
            ]);
        }
        return response()->json([
            "data" => $settings,
            "code" => 200
        ], 200);
    }

    public function listQuestionnaire()
    {
        $questionsAll = Question::with("answers")->orderBy("id", "asc")
            ->get();
        $questionaireUser = collect($questionsAll)->map(function ($question) {
            $answers = $this->getAnswer($question->answers);
            unset($question->answers);
            $question["answers"] = $answers;
            $question["options"] = json_decode($question->options);
            return $question;
        })->values();
        return response()->json($questionaireUser, 200);
    }

    public function getAnswer($answers)
    {
        if (isset($answers)) return json_decode($answers->answers);
        return collect([]);
    }

    public function createQuestion(Request $request)
    {
        $rules = [
            "question" => "required",
            "type" => "required",
        ];
        $message = [
            "question.required" => "Question is required!",
            "type.required" => "Type question is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }

        $options = collect([]);
        if (isset($request->options)) {
            $options = collect($request->options)->map(function ($item) {
                $item["key"] = Str::slug($item["text"]);
                return $item;
            });
        }

        Question::create([
            "question" => $request->question,
            "slug" => Str::slug($request->question),
            "type" => $request->type,
            "background" => $request->background,
            "description" => $request->description,
            "options" => json_encode($options),
        ]);

        return response()->json([
            "message" => "Create question successfully!",
            "code" => 200
        ], 200);
    }

    public function finishSurveyQuestion()
    {
        Auth::user()->is_complete_survey = true;
        Auth::user()->save();
        return response()->json([
            "message" => "success",
            "code" => 200
        ], 200);
    }

    public function updateQuestion(Request $request, $id)
    {
        $rules = [
            "question" => "required",
            "type" => "required",
        ];
        $message = [
            "question.required" => "Question is required!",
            "type.required" => "Type question is required!",
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        }

        $options = collect([]);
        if (isset($request->options)) {
            $options = collect($request->options)->map(function ($item) {
                $item["key"] = Str::slug($item["text"]);
                return $item;
            });
        }

        $question = Question::find($id);
        $question->question = $request->question;
        $question->slug = Str::slug($request->question);
        $question->type = $request->type;
        $question->options = json_encode($options);
        $question->save();

        return response()->json([
            "message" => "Update question successfully!",
            "code" => 200
        ], 200);
    }

    public function updateQuestionaireUser(Request $request, $id)
    {
        $questionareUser = QuestionnaireUser::query()->where("questionId", $id)->where("userId", Auth::user()->id)->first();
        if (isset($questionareUser)) {
            $questionareUser->answers = $request->input("answers");
            $questionareUser->save();
            return;
        }
        QuestionnaireUser::create([
            "answers" => json_encode($request->input("answers")),
            "userId" => Auth::user()->id,
            "questionId" => $id,
        ]);
    }

    public function deleteQuestion($id)
    {
        Question::query()
            ->where("id", $id)
            ->delete();

        return response()->json([
            "message" => "Delete question successfully!",
            "code" => 200
        ], 200);
    }
}
