<?php

namespace EscolaLms\Recommender\Services;

use EscolaLms\Recommender\EscolaLmsRecommenderServiceProvider;
use EscolaLms\Recommender\Models\Topic;
use EscolaLms\Recommender\Exceptions\RecommenderDisabledException;
use EscolaLms\Recommender\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Recommender\Services\Contracts\RecommenderServiceContract;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use Illuminate\Support\Facades\Http;


class RecommenderService implements RecommenderServiceContract
{
    private TopicRepositoryContract $topicRepository;

    private $class_type_page = [
        'PDF_page',
    ];

    private $class_type_length = [
        'H5P.Summary_length',
        'H5P.ImageHotspotQuestion_length',
        'RichText_length',
        'H5P.DragText_length',
        'H5P.Blanks_length',
        'H5P.TrueFalse_length',
        'H5P.QuestionSet_length',
        'H5P.ImageJuxtaposition_length',
        'H5P.Accordion_length',
        'H5P.DragQuestion_length',
        'H5P.SingleChoiceSet_length',
        'H5P.MultiChoice_length',
        'H5P.GuessTheAnswer_length',
        'H5P.Agamotto_length',
        'H5P.Collage_length',
        'H5P.SortParagraphs_length',
        'H5P.InteractiveVideo_length',
        'H5P.ImageHotspots_length',
        'H5P.Dialogcards_length',
        'H5P.Essay_length',
        'PDF_length',
        'Video_length',
        'H5P.ImageSlider_length',
    ];

    private $class_type = [
        'OEmbed',
        'H5P.Summary',
        'H5P.FindTheWords',
        'H5P.ImageHotspotQuestion',
        'RichText',
        'H5P.DragText',
        'H5P.Blanks',
        'H5P.TrueFalse',
        'H5P.QuestionSet',
        'H5P.Crossword',
        'H5P.ImageJuxtaposition',
        'H5P.Accordion',
        'H5P.CoursePresentation',
        'H5P.MultiMediaChoice',
        'H5P.DragQuestion',
        'H5P.SingleChoiceSet',
        'H5P.MultiChoice',
        'H5P.Column',
        'Image',
        'H5P.GuessTheAnswer',
        'H5P.MemoryGame',
        'H5P.Flashcards',
        'H5P.Chart',
        'H5P.Cornell',
        'H5P.InteractiveBook',
        'H5P.Dialogcards',
        'H5P.Agamotto',
        'H5P.Collage',
        'H5P.ImageSequencing',
        'H5P.SortParagraphs',
        'H5P.InteractiveVideo',
        'H5P.Audio',
        'H5P.ImageHotspots',
        'H5P.Timeline',
        'H5P.BranchingScenario',
        'H5P.InfoWall',
        'H5P.Essay',
        'PDF',
        'Video',
        'H5P.MarkTheWords',
        'H5P.PersonalityQuiz',
        'H5P.ImageSlider',
        'H5P.KewArCode',
        'H5P.ImagePair',
    ];

    public function __construct(
        TopicRepositoryContract $topicRepository
    )
    {
        $this->topicRepository = $topicRepository;
    }

    public function completionOfCourse(int $courseId): array
    {
        return $this->getResult('course', $this->makeCourseData($courseId));
    }

    public function matchTopicType(int $lessonId): array
    {
        return $this->getResult('exercise/match', $this->makeTopicData($lessonId));
    }

    public function makeCourseData(int $courseId): array
    {
        if (!config(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.course_model')) {
            throw new RecommenderDisabledException('Recommender course model is not set!');
        }

        $topics = $this->topicRepository->getAllByCourseId($courseId);

        $keys = $topics
            ->map(fn($topic) => [
                $this->getDatasetKey($topic),
                $this->getDatasetKey($topic, $this->class_type_length, '_length'),
                $this->getDatasetKey($topic, $this->class_type_page, '_page'),
            ])
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->toArray();


        $dataset = array_fill_keys($keys, 0);

        foreach ($topics as $topic) {
            $key = $this->getDatasetKey($topic);

            if (empty($key)) {
                continue;
            }
            if (key_exists($key, $dataset)) {
                $dataset[$key]++;
            }
            if (key_exists($key . '_length', $dataset)) {
                $dataset[$key . '_length'] += $topic->topicable->length;
            }
            if (key_exists($key . '_page', $dataset)) {
                $dataset[$key . '_page'] += $topic->topicable->page;
            }
        }

        return $dataset;
    }

    public function makeTopicData(int $lessonId): array
    {
        if (!config(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.exercise_model')) {
            throw new RecommenderDisabledException('Recommender exercise model is not set!');
        }

        $limit = 5;
        $marker = 1.0;
        $topics = $this->topicRepository->getAllByLessonId($lessonId);
        $courseId = $topics->first()->lesson->course_id;
        $topicsCount = $this->topicRepository->countTopicByCourseId($courseId);
        $topicsLimit = $topics->take($limit);
        $step = min($limit, $topicsLimit->count());

        $dataset = [
            'question_number' => ++$topicsCount
        ];

        /* @var $topicsLimit Topic[] */
        foreach ($topicsLimit as $topic) {
            $key = $this->getDatasetKey($topic);

            if (empty($key)) {
                $step--;
                continue;
            }

            $dataset[$key . '_' . $step] = $marker;
            $step--;
        }

        return $dataset;
    }

    private function getDatasetKey(Topic $topic, ?array $filterBy = [], ?string $suffix = null): ?string
    {
        if (!$filterBy) {
            $filterBy = $this->class_type;
        }

        $key = class_basename($topic->topicable) . $suffix;

        if ($topic->topicable_type === H5P::class) {
            $key = $topic->topicable->libraryName . $suffix;
        }

        return in_array($key, $filterBy) ? $key : null;
    }

    /**
     * @throws RecommenderDisabledException
     */
    private function getResult(string $url, array $data): ?array
    {
        if (!config(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.api_url')) {
            throw new RecommenderDisabledException('Recommender API URL is not set!');
        }

        $body = empty($data) ? (object) [] : $data;

        return Http::withBody(json_encode($body), 'application/json')
            ->post(config(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.api_url') . $url)
            ->throw()
            ->collect()
            ->get('data');
    }
}
