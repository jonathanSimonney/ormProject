<?php
namespace entity;

use Library\BaseEntity;

/**
 * Class Seance
 * @Table seances
 */
class Seance extends BaseEntity
{
    /**
     * @var int
     * @Id @Column integer
     */
    protected $id;

    /**
     * @var \DateTime
     * @Column Datetime
     */
    protected $showtime;

    /**
     * @var Film
     * @ManyToOne Film
     * @InversedBy seances
     */
    protected $film;

    /**
     * @return int | null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getShowtime(): ?\DateTime
    {
        return $this->showtime;
    }

    /**
     * @param \DateTime $showtime
     */
    public function setShowtime(\DateTime $showtime)
    {
        $this->showtime = $showtime;
    }

    /**
     * @return Film
     */
    public function getFilm()
    {
        return $this->film;
    }

    /**
     * @param Film $film
     */
    public function setFilm($film)
    {
        $this->film = $film;
    }
}