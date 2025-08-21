<?php

namespace App\Services;

use App\Contracts\SimuladoQuestionServiceInterface;
use App\Models\Simulado;
use App\Models\SimuladoQuestion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimuladoQuestionService implements SimuladoQuestionServiceInterface
{
    public function createQuestions(Simulado $simulado, array $questionsData): Collection
    {
        $questions = collect();
        
        DB::transaction(function () use ($simulado, $questionsData, &$questions) {
            foreach ($questionsData as $index => $questionData) {
                $validatedData = $this->validateQuestionData($questionData);
                
                $question = SimuladoQuestion::create([
                    'simulado_id' => $simulado->id,
                    'statement' => $validatedData['statement'],
                    'q_type' => $validatedData['q_type'] ?? 'multiple_choice',
                    'weight' => $validatedData['weight'] ?? 1,
                    'difficulty' => $validatedData['difficulty'] ?? 'medium',
                    'options' => $validatedData['options'],
                    'correct_answer' => $validatedData['correct_answer'],
                    'explanation' => $validatedData['explanation'] ?? null,
                    'q_order' => $index + 1,
                ]);
                
                $questions->push($question);
            }
        });
        
        return $questions;
    }

    public function updateQuestions(Simulado $simulado, array $questionsData): Collection
    {
        return DB::transaction(function () use ($simulado, $questionsData) {
            // Remove todas as questões existentes
            $this->deleteAllQuestions($simulado);
            
            // Cria as novas questões
            return $this->createQuestions($simulado, $questionsData);
        });
    }

    public function deleteAllQuestions(Simulado $simulado): bool
    {
        return $simulado->questions()->delete() > 0;
    }

    public function validateQuestionData(array $questionData): array
    {
        $rules = [
            'statement' => 'required|string|max:1000|min:10',
            'q_type' => 'nullable|string|in:multiple_choice,true_false,essay,ordering,matching',
            'options' => 'required_if:q_type,multiple_choice|array|min:2|max:6',
            'options.*' => 'required|string|max:500',
            'correct_answer' => 'required|string|max:10',
            'weight' => 'nullable|integer|min:1|max:10',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
            'explanation' => 'nullable|string|max:1000',
        ];

        $validator = validator($questionData, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    public function reorderQuestions(Simulado $simulado, array $questionIds): bool
    {
        return DB::transaction(function () use ($simulado, $questionIds) {
            foreach ($questionIds as $index => $questionId) {
                SimuladoQuestion::where('simulado_id', $simulado->id)
                    ->where('id', $questionId)
                    ->update(['q_order' => $index + 1]);
            }
            
            return true;
        });
    }

    public function duplicateQuestions(Simulado $sourceSimulado, Simulado $targetSimulado): Collection
    {
        $sourceQuestions = $sourceSimulado->questions;
        $questionsData = [];
        
        foreach ($sourceQuestions as $question) {
            $questionsData[] = [
                'statement' => $question->statement,
                'q_type' => $question->q_type,
                'weight' => $question->weight,
                'difficulty' => $question->difficulty,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation,
            ];
        }
        
        return $this->createQuestions($targetSimulado, $questionsData);
    }
}