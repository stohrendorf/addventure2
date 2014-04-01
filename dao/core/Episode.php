<?php

namespace addventure;

/**
 * @Entity(repositoryClass="\addventure\EpisodeRepository")
 * @Table(
 *     indexes={
 *         @Index(name="parentIndex", columns={"parent_id"}),
 *         @Index(name="authorIndex", columns={"author_id"}),
 *         @Index(name="storylineIndex", columns={"storyline_tag_id"})
 *     },
 *     uniqueConstraints={
 *         @UniqueConstraint(name="oldIdIndex", columns={"oldId"})
 *     }
 * )
 */
class Episode implements IAddventure {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer", nullable=false)
     * @var int
     */
    private $oldId = null;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    private $title = null;

    /**
     * @Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $created = null;

    /**
     * @Column(type="text", nullable=true)
     * @var string
     */
    private $notes = null;

    /**
     * @Column(type="text", nullable=true)
     * @var string
     */
    private $preNotes = null;

    /**
     * @Column(type="text", nullable=true)
     * @var string
     */
    private $text = null;

    /**
     * @Column(type="integer", nullable=false)
     * @var int
     */
    private $hitCount = 0;

    /**
     * @Column(type="integer", nullable=false)
     * @var int
     */
    private $likes = 0;

    /**
     * @Column(type="integer", nullable=false)
     * @var int
     */
    private $dislikes = 0;

    /**
     * @ManyToOne(targetEntity="addventure\Episode", fetch="LAZY")
     * @var Episode
     */
    private $parent = null;

    /**
     * @ManyToOne(targetEntity="addventure\AuthorName", inversedBy="episodes", cascade={"PERSIST"})
     * @JoinColumn(name="author_id", referencedColumnName="id")
     * @var AuthorName
     */
    private $author = null;

    /**
     * @ManyToOne(targetEntity="addventure\StorylineTag", inversedBy="episodes")
     * @JoinColumn(name="storyline_tag_id", referencedColumnName="id")
     * @var StorylineTag
     */
    private $storylineTag = null;

    /**
     * @ManyToMany(targetEntity="addventure\SimpleTag", mappedBy="episodes", cascade={"PERSIST","REMOVE"})
     * @var SimpleTag[]
     */
    private $simpleTags = null;

    /**
     * @Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $linkable = false;

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getPreNotes() {
        return $this->preNotes;
    }

    public function getText() {
        return $this->text;
    }

    public function getHitCount() {
        return $this->hitCount;
    }

    public function getLikes() {
        return $this->likes;
    }

    public function getDislikes() {
        return $this->dislikes;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getStorylineTag() {
        return $this->storylineTag;
    }

    public function getSimpleTags() {
        return $this->simpleTags;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTitle($title) {
        if(mb_strlen($title) > 255) {
            throw new \InvalidArgumentException("Title too long");
        }
        $this->title = $title;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    public function setPreNotes($notes) {
        $this->preNotes = $notes;
        return $this;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }

    public function setHitCount($hitCount) {
        $this->hitCount = $hitCount;
        return $this;
    }

    public function setLikes($likes) {
        $this->likes = $likes;
        return $this;
    }

    public function setDislikes($dislikes) {
        $this->dislikes = $dislikes;
        return $this;
    }

    public function setParent(Episode $parent) {
        $this->parent = $parent;
        return $this;
    }

    public function setAuthor(AuthorName $author) {
        $this->author = $author;
        return $this;
    }

    public function setStorylineTag(StorylineTag $storylineTag) {
        $this->storylineTag = $storylineTag;
        return $this;
    }

    public function setSimpleTags($simpleTag) {
        $this->simpleTags = $simpleTag;
        return $this;
    }

    public function getOldId() {
        return $this->oldId;
    }

    public function setOldId($oldId) {
        $this->oldId = $oldId;
        return $this;
    }

    public function getLinkable() {
        return $this->linkable;
    }

    public function setLinkable($linkable) {
        $this->linkable = $linkable;
        return $this;
    }
    
    public function addSimpleTag(SimpleTag $tag) {
        if($tag===null) {
            return;
        }
        if(!$this->simpleTags) {
            $this->simpleTags = array();
        }
        $this->simpleTags[] = $tag;
    }

    public function toJson() {
        $tmp = array(
            'id' => $this->getId(),
            'title' => $this->getTitle()
        );
        if(($c = $this->getCreated())) {
            $tmp['created'] = $c->format('c');
        }
        if(($a = $this->getAuthor())) {
            $tmp['author'] = $a->toJson();
        }
        return $tmp;
    }

    public function toSmarty() {
        $tmp = array(
            'id' => $this->getId(),
            'title' => $this->getTitle()
        );
        if(($c = $this->getCreated())) {
            $tmp['created'] = $c->format("l, d M Y H:i");
        }
        if(($a = $this->getAuthor())) {
            $tmp['author'] = $a->toSmarty();
        }
        $tmp['text'] = $this->getText();
        $tmp['hitcount'] = $this->getHitCount();
        $tmp['likes'] = $this->getLikes();
        $tmp['dislikes'] = $this->getDislikes();
        $tmp['notes'] = $this->getNotes();
        $tmp['preNotes'] = $this->getPreNotes();
        $tmp['linkable'] = $this->getLinkable();
        if(($p = $this->getParent())) {
            $tmp['parent'] = $p->getId();
        }
        global $entityManager; // HACK
        $tmp['children'] = array();
        $dql = 'SELECT l FROM addventure\Link l WHERE l.fromEp=?1 ORDER BY l.toEp';
        $q = $entityManager->createQuery($dql)->setParameter(1, $this->getId());
        foreach($q->getResult() as $child) {
            $tmp['children'][] = $child->toSmarty();
        }
        $tmp['backlinks'] = array();
        $dql = 'SELECT l FROM addventure\Link l WHERE l.toEp=?1 AND l.isBacklink=TRUE ORDER BY l.fromEp';
        $q = $entityManager->createQuery($dql)->setParameter(1, $this->getId());
        foreach($q->getResult() as $child) {
            $tmp['backlinks'][] = $child->toSmarty();
        }
        return $tmp;
    }

    public function toRss(\SimpleXMLElement &$channel) {
        $item = $channel->addChild('item');
        $t = $this->getTitle();
        $item->addChild('title', empty($t) ? '&lt;No title specified&gt;' : htmlspecialchars($t));
        $item->addChild('link', htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]) . '?doc=' . $this->getId()));
        $a = $this->getAuthor();
        $item->addChild('author', $a ? htmlspecialchars($a->getName()) : '');
        $item->addChild('guid', 'addventure:episode:' . $this->getId());
        $item->addChild('pubDate', $this->getCreated() ? $this->getCreated()->format(\DateTime::RSS) : '');
    }

    public function toAtom(\SimpleXMLElement &$feed) {
        $entry = $feed->addChild('entry');
        $entry->addChild('id', 'addventure:episode:' . $this->getId());
        $t = $this->getTitle();
        $entry->addChild('title', empty($t) ? '&lt;No title specified&gt;' : htmlspecialchars($t));
        $entry->addChild('updated', $this->getCreated() ? $this->getCreated()->format(\DateTime::ATOM) : '');
        $l = $entry->addChild('link');
        $l->addAttribute('rel', 'alternate');
        $l->addAttribute('href', dirname($_SERVER["REQUEST_URI"]) . '?doc=' . $this->getId());
        $a = $this->getAuthor();
        if($a) {
            $a->toAtom($entry);
        }
    }

}

class EpisodeRepository extends \Doctrine\ORM\EntityRepository {

    public function getRecentEpisodes($count, $page = null) {
        if(!is_numeric($count) || $count < 1 || $count > ADDVENTURE_RESULTS_PER_PAGE) {
            $count = ADDVENTURE_RESULTS_PER_PAGE;
        }

        if($page === false || $page === null) {
            $page = 0;
        }
        elseif(!is_numeric($page)) {
            throw new \InvalidArgumentException('Page is not numeric.');
        }

        $dql = 'SELECT e FROM addventure\Episode e WHERE e.text IS NOT NULL ORDER BY e.created DESC';
        $qb = $this->getEntityManager()->createQuery($dql)->setFirstResult($page * $count)->setMaxResults($count);
        return new \Doctrine\ORM\Tools\Pagination\Paginator($qb, false);
    }

    public function getRecentEpisodesByUser($count, $user, $page = null) {
        if(!is_numeric($count) || $count < 1 || $count > ADDVENTURE_MAX_RECENT) {
            $count = ADDVENTURE_MAX_RECENT;
        }

        if($page === false || $page === null) {
            $page = 0;
        }
        elseif(!is_numeric($page)) {
            throw new \InvalidArgumentException('Page is not numeric.');
        }

        $user = $this->getEntityManager()->find('addventure\User', $user);
        if(!$user) {
            return NULL;
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')->from('addventure\Episode', 'e')->where('e.text IS NOT NULL')->orderBy('e.created', 'DESC');
        foreach($user->getAuthorNames() as $a) {
            $qb->orWhere('e.author=' . $a->getId());
        }
        $qb->setFirstResult($page * $count);
        $qb->setMaxResults($count);
        global $logger;
        $logger->debug('First result: ' . $page * $count);
        $qb = $qb->getQuery();
        return new \Doctrine\ORM\Tools\Pagination\Paginator($qb, false);
    }

    public function findByUser($userId, callable $func, $page = 0) {
        if(!is_numeric($userId)) {
            throw new \InvalidArgumentException('User ID is not numeric.');
        }
        if($page === false || $page === null) {
            $page = 0;
        }
        elseif(!is_numeric($page)) {
            throw new \InvalidArgumentException('Page is not numeric.');
        }
        $user = $this->getEntityManager()->find('addventure\User', $userId);
        if(!$user) {
            return 0;
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->setFirstResult($page * ADDVENTURE_RESULTS_PER_PAGE);
        $qb->setMaxResults(ADDVENTURE_RESULTS_PER_PAGE);
        global $logger;
        $logger->debug('First result: ' . $page * ADDVENTURE_RESULTS_PER_PAGE);
        $qb->select('e')->from('addventure\Episode', 'e')->orderBy('e.created', 'DESC');
        foreach($user->getAuthorNames() as $a) {
            $qb->orWhere('e.author=' . $a->getId());
        }
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery(), false);
        foreach($paginator as $ep) {
            $func($ep);
            $this->getEntityManager()->detach($ep);
            $this->getEntityManager()->clear();
        }
        return $paginator->count();
    }

    public function firstCreatedByUser($userId) {
        if(!is_numeric($userId)) {
            throw new \InvalidArgumentException('User ID is not numeric.');
        }
        $user = $this->getEntityManager()->find('addventure\User', $userId);
        if(!$user) {
            return NULL;
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('MIN(e.created) AS minDate')->from('addventure\Episode', 'e');
        foreach($user->getAuthorNames() as $a) {
            $qb->orWhere('e.author=' . $a->getId());
        }
        $res = $qb->getQuery()->getOneOrNullResult();
        if($res != null) {
            return new \DateTime($res['minDate']);
        }
        return null;
    }

    public function lastCreatedByUser($userId) {
        if(!is_numeric($userId)) {
            throw new \InvalidArgumentException('User ID is not numeric.');
        }
        $user = $this->getEntityManager()->find('addventure\User', $userId);
        if(!$user) {
            return NULL;
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('MAX(e.created) AS maxDate')->from('addventure\Episode', 'e');
        foreach($user->getAuthorNames() as $a) {
            $qb->orWhere('e.author=' . $a->getId());
        }
        $res = $qb->getQuery()->getOneOrNullResult();
        if($res != null) {
            return new \DateTime($res['maxDate']);
        }
        return null;
    }

}
