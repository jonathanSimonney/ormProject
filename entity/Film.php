<?php
namespace entity;

use Library\BaseEntity;

/**
 * Class Film
 * @Table films
 */

class Film extends BaseEntity
{
    /**
     * @var int
     * @Id @Column integer
     */
    protected $id;

    /**
     * @var string
     * @Column string
     * @ColumnName test_column_title
     */
    protected $title;

    /**
     * @var ?string
     * @Column string
     * @Nullable true
     */
    protected $director;

    /**
     * @var \DateTime
     * @Column date
     */
    protected $releaseDate;

    /**
     * @var ?string
     * @Column string
     * @Nullable true
     */
    protected $genre;

    /**
     * @var int
     * @Column integer
     */
    protected $duration;

    /**
     * @var Seance[]
     * @OneToMany Seance
     * @MappedBy film
     */
    protected $seances;

    public function __construct()
    {
        $this->seances = [];
    }

    /**
     * @return int | null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDirector(): string
    {
        return $this->director;
    }

    /**
     * @param string $director
     */
    public function setDirector(string $director)
    {
        $this->director = $director;
    }

    /**
     * @return \DateTime
     */
    public function getReleaseDate(): \DateTime
    {
        return $this->releaseDate;
    }

    /**
     * @param \DateTime $releaseDate
     */
    public function setReleaseDate(\DateTime $releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return string
     */
    public function getGenre(): ?string
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     */
    public function setGenre(?string $genre)
    {
        $this->genre = $genre;
    }

    /**
     * @return int
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(?int $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return Seance[]
     */
    public function getSeances(): array
    {
        return $this->seances;
    }

    /**
     * @param Seance[] $seances
     */
    public function setSeances(array $seances)
    {
        $this->seances = $seances;
    }

    public function addSeance($seance){
        $this->seances[] = $seance;
    }
}