<?php

namespace App\Http\Controllers\Api\V1;

use App\Specimen;
use App\Header;
use App\Character;
use App\Value;
use App\ActionLog;
use App\ActivityLog;
use App\MetaLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class HomeController extends Controller
{
    public function getValuesByCharacter($userId)
    {
        $all = Character::where('user_id', '=', $userId)->get();
        $characters = [];
        foreach ($all as $each) {
            $tpValues = Value::where('character_id', '=', $each->id)->orderBy('header_id', 'dec')->get();
            $characters []= $tpValues;
        }

        return $characters;
    }

    public function getCharacter(Request $request, $id)
    {
        $character = Character::where('id', '=', $id)->first();

        return $character;
    }

    public function store(Request $request)
    {
        if ($request->has('id')) {
            $character = Character::where('id', '=', $request->input('id'))->first();
            $character->name = $request->input('name');
            $character->method_as = $request->input('method_as');
            $character->method_from = $request->input('method_from');
            $character->method_to = $request->input('method_to');
            $character->unit = $request->input('unit');
            $character->measure_semantic = $request->input('measure_semantic');
            $character->entity_semantic = $request->input('entity_semantic');
            $character->creator = $request->input('creator');
            $character->user_id = $request->input('user_id');
            $character->save();

        } else {
            $character = Character::create([
                'name' => $request->input('name'),
                'method_as' => $request->input('method_as'),
                'method_from' => $request->input('method_from'),
                'method_to' => $request->input('method_to'),
                'unit' => $request->input('unit'),
                'measure_semantic' => $request->input('measure_semantic'),
                'entity_semantic' => $request->input('entity_semantic'),
                'creator' => $request->input('creator'),
                'user_id' => $request->input('user_id')
            ]);
//            $headers = Header::orderBy('created_at', 'dec')->get();
            $headers = Header::where('user_id', '=', $request->input('user_id'))->orWhere('user_id', '=', null)->get();
            foreach ($headers as $header) {
                Value::create([
                    'character_id' => $character->id,
                    'header_id' => $header->id,
                    'value' => ''
                ]);
            }
        }

        // update character header in Value Model
        $value = Value::where('character_id', '=', $character->id)->where('header_id', '=', 1)->first();
        $value->value = $character->name;
        $value->save();

        $characters = $this->getValuesByCharacter($request->input('user_id'));
        $arrayCharacters = Character::where('user_id', '=', $request->input('user_id'))->get();
        $data = [
            'character'  => $character,
            'value'       => $value,
            'characters'    => $characters,
            'arrayCharacters' => $arrayCharacters
        ];

        return $data;
    }

    public function history(Request $request, $characterId)
    {
        $history = ActionLog::select('created_at')
            ->where('model_id', '=', $characterId)
            ->whereIn('action_type', ['update', 'create'])
            ->get();

        return $history;
    }

    public function getName(Request $request, $userId)
    {
        $characterName = Character::select('name')->where('user_id', '=', $userId)->get();

        return $characterName;
    }

    public function usage(Request $request, $characterId)
    {
        $values = Value::where('character_id', '=', $characterId)
            ->where('header_id', '>', 3)
            ->where('value', '<>', '')
            ->get();

        $usage = [];

        if (count($values) > 0) {
            foreach ($values as $each) {
                $tpUsage = Header::select('header')
                    ->where('id', '=', $each->header_id)
                    ->first();
                $usage []= $tpUsage;
            }
        }

        return $usage;
    }

    public function log(Request $request)
    {
        $actionLog = ActionLog::create($request->all());

        return $actionLog;
    }

    public function all(Request $request, $userId)
    {
        $headers = Header::where('user_id', '=', $userId)->orWhere('user_id', '=', null)->orderBy('created_at', 'dec')->get();
//        $headers = Header::all();
        $characters = $this->getValuesByCharacter($userId);
        $arrayCharacters = Character::where("user_id", '=', $userId)->get();

        $data = [
            'headers'               => $headers,
            'characters'            => $characters,
            'arrayCharacters'       => $arrayCharacters
        ];

        return $data;
    }

    public function addHeader(Request $request, $userId) {
        $header = Header::create([
                'header' => $request->input('header'),
                'user_id' => $userId
        ]);
        $characters = Character::where('user_id', '=', $userId)->get();
        foreach ($characters as $character) {
            Value::create([
                'character_id' => $character->id,
                'header_id' => $header->id,
                'value' => ''
            ]);
        }

        $headers = Header::where('user_id', '=', $userId)->orWhere('user_id', '=', null)->orderBy('created_at', 'dec')->get();
//        $headers = Header::all();
        $characters = $this->getValuesByCharacter($userId);
        $arrayCharacters = Character::where('user_id', $userId)->get();

        $data = [
            'headers'       => $headers,
            'characters'    => $characters,
            'arrayCharacters'       => $arrayCharacters
        ];

        return $data;
    }

    public function update(Request $request) {
        $value = Value::where('id', '=', $request->input('id'))->first();
        $value->value = $request->input('value');
        $value->save();

        return $value;
    }

    public function delete(Request $request, $userId) {
        $character_id = $request->input('character_id');
        Character::where('id', '=', $character_id)->delete();
        Value::where('character_id', '=', $character_id)->delete();
        $characters = $this->getValuesByCharacter($userId);
        $arrayCharacters = Character::where('user_id', $userId)->get();
        $data = [
            'characters'    => $characters,
            'arrayCharacters' => $arrayCharacters
        ];

        return $data;
    }

    public function activity_log(Request $request) {
        
        $actLog = ActivityLog::create($request->all());

        return $actLog;
    }

    public function saveMetaLog(Request $request) {
        $metaLog = MetaLog::create($request->all());

        return $metaLog;
    }

    public function getMetaLog(Request $request, $characterId) {
        $metaLogs = MetaLog::where('character_id', '=', $characterId)->orderBy('created_at', 'asc')->get();


        $metaHistory = [];
        foreach ($metaLogs as $eachLog) {
            $tpValue = $eachLog->created_at . ' ' . $eachLog->username . ' ' . $eachLog->description;
            $metaHistory []= $tpValue;
        }

        $data = [
            'metaHistory'   =>  $metaHistory
        ];

        return $data;
    }
}
