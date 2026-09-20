<?php

namespace App\Http\Controllers;

use App\Services\DopaService;
use Illuminate\Http\Request;

class DopaController extends Controller
{
    public function show(
        Request $request,
        DopaService $dopaService
    ) {
        $user = $request->user();

        $dopa = $dopaService->getOrCreate($user);

        return response()->json([
            'id' => $dopa->id,
            'name' => $dopa->name,
            'experience' => $dopa->experience,
            'level' => $dopa->level,
            'stage' => $dopa->stage,
            'stage_name' => $dopaService->stageName($dopa->stage),
            'type' => $dopa->type,

            'next_level_experience' =>
                $dopa->level >= DopaService::MAX_LEVEL
                    ? null
                    : $dopaService->requiredExperience($dopa->level + 1),

            'experience_to_next_level' =>
                $dopaService->experienceToNextLevel($dopa),

            'progress_percentage' =>
                $dopaService->progressPercentage($dopa),

            'next_evolution_level' =>
                $dopaService->nextEvolutionLevel($dopa->level),

            'levels_to_next_evolution' =>
                $dopaService->levelsToNextEvolution($dopa->level),
        ]);
    }

    public function update(
        Request $request,
        DopaService $dopaService
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
        ]);
    
        $dopa = $dopaService->getOrCreate($request->user());
    
        $dopa->update([
            'name' => $validated['name'],
        ]);
    
        return response()->json([
            'name' => $dopa->name,
        ]);
    }
}