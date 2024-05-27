<?php

namespace App\Livewire;

use App\Models\Jawaban;
use App\Models\Peserta;
use Livewire\Component;
use App\Models\Soal as SoalModel;
use Livewire\Attributes\Validate;

class Soal extends Component
{
    public SoalModel $soal;
    public Peserta $peserta;

    #[Validate('required', message: 'Jawaban wajib diisi')]

    public array $jawabans = [];

    public int $total_nilai;

    public int $jawaban_berpoin;

    public bool $passed = false;

    public bool $status_jawaban = false;

    public function render()
    {
        return view('livewire.soal');
    }


    public function mount(SoalModel $soal, Peserta $peserta)
    {
        $this->soal = $soal;
        $this->peserta = $peserta;
    }

    public function getScoreByTotalQuestion()
    {
        try {
            $total_soal = $this->soal->pertanyaans->count();
            $total_nilai = $this->soal->total_nilai;

            return $total_nilai / $total_soal;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function countScore()
    {
        try {
            $this->total_nilai = 0;
            $this->jawaban_berpoin = 0;

            foreach ($this->soal->pertanyaans as $pertanyaan) {
                if ($pertanyaan->jawaban === (int) $this->jawabans[$pertanyaan->id]) {
                    $this->total_nilai += $this->getScoreByTotalQuestion();
                    $this->jawaban_berpoin++;
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function checkPassingGrade()
    {
        if ($this->total_nilai >= $this->soal->passing_grade) {
            $this->passed = true;
        }
    }

    public function submitJawaban()
    {
        $this->validate();
        $this->countScore();
        $this->checkPassingGrade();

        $total_nilai = $this->total_nilai;
        $user_id = $this->peserta->getKey();
        $passed = $this->passed;
        $soal_id = $this->soal->getKey();

        $jawaban = Jawaban::create([
            'peserta_id' => $user_id,
            'status_jawaban' => true,
            'soal_id' => $soal_id,
            'nilai_peserta' => $total_nilai
        ]);

        return redirect()->route('result', ['jawaban'=> $jawaban]);
    }
}
