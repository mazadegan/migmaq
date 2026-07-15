<?php
require_once __DIR__ . '/../models/Unit.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Lesson.php';

class ContentsController
{
    private Unit $unitModel;
    private Section $sectionModel;
    private Lesson $lessonModel;

    public function __construct(PDO $pdo)
    {
        $this->unitModel = new Unit($pdo);
        $this->sectionModel = new Section($pdo);
        $this->lessonModel = new Lesson($pdo);
    }

    public function show(): void
    {
        $units = $this->getContentsTree();
        require __DIR__ . '/../views/contents.php';
    }


    public function getContentsTree(): array
    {
        $units = $this->unitModel->all(true);

        foreach ($units as &$unit) {
            $sections = $this->sectionModel->getByUnit($unit['id'], true);
            foreach ($sections as &$section) {
                $section['lessons'] = $this->lessonModel->getBySection($section['id'], true);
            }
            $unit['sections'] = $sections;
        }

        return $units;
    }
}
