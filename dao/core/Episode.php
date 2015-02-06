<?php

namespace addventure;

/**
 * @Entity(repositoryClass="\addventure\EpisodeRepository")
 * @Table(
 *     indexes={
 *         @Index(name="parentIndex", columns={"parent_id"}),
 *         @Index(name="authorIndex", columns={"author_id"}),
 *         @Index(name="storylineIndex", columns={"storyline_tag_id"}),
 *         @Index(name="createdIndex", columns={"created"})
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
    private $postNotes = null;

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
     * @ManyToMany(targetEntity="addventure\SimpleTag", mappedBy="episodes")
     * @var SimpleTag[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $simpleTags;

    /**
     * @Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $linkable = false;

    /**
     * @OneToMany(targetEntity="addventure\Comment", mappedBy="episode", cascade={"PERSIST","REMOVE"}, orphanRemoval=true, fetch="LAZY")
     * @OrderBy({"created" = "ASC"})
     * @var Comment[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $comments = null;

    public function __construct() {
        $this->simpleTags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getComments() {
        return $this->comments;
    }

    public function setComments($comments) {
        $this->comments = $comments;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getPostNotes() {
        return $this->postNotes;
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
        $this->title = simplifyWhitespace($title, 255);
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setPostNotes($notes) {
        $this->postNotes = empty($notes) ? null : $notes;
        return $this;
    }

    public function setPreNotes($notes) {
        $this->preNotes = empty($notes) ? null : $notes;
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

    public function getLinkable() {
        return $this->linkable;
    }

    public function setLinkable($linkable) {
        $this->linkable = $linkable;
        return $this;
    }

    /**
     * An automatically generated title if the original title is empty, or the original title.
     * @return string
     */
    public function getAutoTitle() {
        if(!empty($this->title)) {
            return $this->title;
        }
        return '#' . $this->id;
    }

    public function toJson() {
        $tmp = array(
            'id' => $this->getId(),
            'title' => $this->getTitle()
        );
        if(($created = $this->getCreated())) {
            $tmp['created'] = $created->format('c');
        }
        if(($author = $this->getAuthor())) {
            $tmp['author'] = $author->toJson();
        }
        return $tmp;
    }

    private static function createTree(array &$dest, \addventure\Episode &$episode, $depth = 0) {
        if($depth > 2) {
            return null;
        }
        $CI = & get_instance();
        $CI->load->library('em');
        $childLinks = $episode->getChildLinks();
        $destArr = array('title' => $episode->getAutoTitle(), 'id' => $episode->getId(), 'children' => array());
        foreach($childLinks as $child) {
            $childEp = $CI->em->findEpisode($child->getToEp());
            if($childEp->getText() === null) {
                continue;
            }
            self::createTree($destArr['children'], $childEp, $depth + 1);
            // do some GC...
            $CI->em->getEntityManager()->detach($childEp);
            unset($childEp);
        }
        $dest[] = $destArr;
    }

    /**
     * Get the direct child links of an episode, excluding backlinks.
     * @param int $from Source episode ID
     * @return Episode[]
     */
    public function getChildLinks() {
        $CI = & get_instance();
        $CI->load->library('em');
        return $CI->em->getEntityManager()->createQuery('SELECT l FROM addventure\Link l WHERE l.fromEp=?1 AND l.isBacklink=FALSE ORDER BY l.toEp')
                        ->setParameter(1, $this->getId())
                        ->getResult();
    }

    /**
     * @codeCoverageIgnore
     */
    public function toSmarty() {
        $result = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'autoTitle' => $this->getAutoTitle(),
            'children' => array(),
            'backlinks' => array(),
            'comments' => array(),
            'tags' => array()
        );
        if(($created = $this->getCreated())) {
            $result['created'] = $created->format("l, d M Y H:i");
        }
        if(($author = $this->getAuthor())) {
            $result['author'] = $author->toSmarty();
        }
        $result['text'] = $this->getText();
        $result['hitcount'] = $this->getHitCount();
        $result['likes'] = $this->getLikes();
        $result['dislikes'] = $this->getDislikes();
        $result['postNotes'] = $this->getPostNotes();
        $result['preNotes'] = $this->getPreNotes();
        $result['linkable'] = $this->getLinkable();
        if(($parent = $this->getParent())) {
            $result['parent'] = $parent->getId();
        }

        $CI = & get_instance();
        $CI->load->library('em');
        $query = $CI->em->getEntityManager()->createQuery('SELECT l FROM addventure\Link l WHERE l.fromEp=?1 ORDER BY l.toEp')
                ->setParameter(1, $this->getId());
        foreach($query->getResult() as $child) {
            $childSmarty = $child->toSmarty();
            $childSmarty['subtree'] = array();
            if(!$child->getIsBacklink()) {
                self::createTree($childSmarty['subtree'], $CI->em->findEpisode($child->getToEp()));
                $childSmarty['subtree'] = $childSmarty['subtree'][0]['children'];
            }
            $result['children'][] = $childSmarty;
        }

        $query = $CI->em->getEntityManager()->createQuery('SELECT l FROM addventure\Link l WHERE l.toEp=?1 AND l.isBacklink=TRUE ORDER BY l.fromEp')
                ->setParameter(1, $this->getId());
        foreach($query->getResult() as $child) {
            $result['backlinks'][] = $child->toSmarty();
        }
        foreach($this->getComments() as $cmt) {
            $result['comments'][] = $cmt->toSmarty();
        }
        foreach($this->getSimpleTags() as $tag) {
            $result['tags'][] = array(
                'title' => $tag->getTitle(),
                'id' => $tag->getId()
            );
        }
        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    public function toRss(\SimpleXMLElement &$channel) {
        $item = $channel->addChild('item');
        $item->addChild('title', htmlspecialchars($this->getAutoTitle()));
        $item->addChild('link', site_url('doc/' . $this->getId()));
        $author = $this->getAuthor();
        $item->addChild('author', $author ? htmlspecialchars($author->getName()) : '');
        $item->addChild('guid', 'addventure:episode:' . $this->getId());
        $item->addChild('pubDate', $this->getCreated() ? $this->getCreated()->format(\DateTime::RSS) : '');
    }

    /**
     * @codeCoverageIgnore
     */
    public function toAtom(\SimpleXMLElement &$feed) {
        $entry = $feed->addChild('entry');
        $entry->addChild('id', 'addventure:episode:' . $this->getId());
        $entry->addChild('title', htmlspecialchars($this->getAutoTitle()));
        $entry->addChild('updated', $this->getCreated() ? $this->getCreated()->format(\DateTime::ATOM) : '');
        $link = $entry->addChild('link');
        $link->addAttribute('rel', 'alternate');
        $link->addAttribute('href', site_url('doc/' . $this->getId()));
        $author = $this->getAuthor();
        if($author) {
            $author->toAtom($entry);
        }
    }

}

class EpisodeRepository extends \Doctrine\ORM\EntityRepository {

    /**
     * Get a descending ordered episode list
     * @param string $column The column name to order by
     * @param string $order Either 'ASC' or 'DESC'
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    private function getOrderedEpisodeList($column, $order, $count, $page = null) {
        if(!is_numeric($count) || $count < 1 || $count > ADDVENTURE_RESULTS_PER_PAGE) {
            $count = ADDVENTURE_RESULTS_PER_PAGE;
        }

        if($page === false || $page === null) {
            $page = 0;
        }
        elseif(!is_numeric($page)) {
            throw new \InvalidArgumentException('Page is not numeric.');
        }

        $dql = "SELECT e FROM addventure\Episode e WHERE e.text IS NOT NULL ORDER BY e.$column $order";
        $queryBuilder = $this->getEntityManager()->createQuery($dql)->setFirstResult($page * $count)->setMaxResults($count);
        $queryBuilder->setResultCacheLifetime(60*60); // 1 hour caching
        return new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder, false);
    }

    /**
     * Get the globally recent episodes
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    public function getRecentEpisodes($count, $page = null) {
        return $this->getOrderedEpisodeList('created', 'DESC', $count, $page);
    }

    /**
     * Get the most read episodes
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    public function getMostReadEpisodes($count, $page = null) {
        return $this->getOrderedEpisodeList('hitCount', 'DESC', $count, $page);
    }

    /**
     * Get the most liked episodes
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    public function getMostLikedEpisodes($count, $page = null) {
        return $this->getOrderedEpisodeList('likes', 'DESC', $count, $page);
    }

    /**
     * Get the most hated episodes
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    public function getMostHatedEpisodes($count, $page = null) {
        return $this->getOrderedEpisodeList('dislikes', 'DESC', $count, $page);
    }

    /**
     * Get the recent episodes by one specific user
     * @param string|int $count The number of results per page
     * @param string|int $user The user's id
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('e')->from('addventure\Episode', 'e')->where('e.text IS NOT NULL')->orderBy('e.created', 'DESC');
        foreach($user->getAuthorNames() as $a) {
            $queryBuilder->orWhere('e.author=' . $a->getId());
        }
        $queryBuilder->setFirstResult($page * $count);
        $queryBuilder->setMaxResults($count);
        $query = $queryBuilder->getQuery();
        $query->setResultCacheLifetime(60*60);
        return new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);
    }

    /**
     * Get the users ordered by their episode count
     * @param string|int $count The number of results per page
     * @param string|int $page The page index
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     * @throws \InvalidArgumentException if the page is invalid
     */
    public function getMostEpisodesByUser($count, $page = null) {
        if(!is_numeric($count) || $count < 1 || $count > ADDVENTURE_RESULTS_PER_PAGE) {
            $count = ADDVENTURE_RESULTS_PER_PAGE;
        }

        if($page === false || $page === null) {
            $page = 0;
        }
        elseif(!is_numeric($page)) {
            throw new \InvalidArgumentException('Page is not numeric.');
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u', 'COUNT(e.id) AS episodeCount')
                ->from('addventure\User', 'u')
                ->join('addventure\AuthorName', 'n', 'WITH', 'n.user = u.id')
                ->join('addventure\Episode', 'e', 'WITH', 'e.author = n.id')
                ->where('e.text IS NOT NULL')
                ->groupBy('u.id')
                ->orderBy('episodeCount', 'DESC');
        $queryBuilder->setFirstResult($page * $count);
        $queryBuilder->setMaxResults($count);
        $query = $queryBuilder->getQuery();
        $query->setResultCacheLifetime(60*60);
        $result = new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);

        return $result;
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->setFirstResult($page * ADDVENTURE_RESULTS_PER_PAGE);
        $queryBuilder->setMaxResults(ADDVENTURE_RESULTS_PER_PAGE);
        $queryBuilder->select('e')->from('addventure\Episode', 'e')->orderBy('e.created', 'DESC');
        foreach($user->getAuthorNames() as $a) {
            $queryBuilder->orWhere('e.author=' . $a->getId());
        }
        $query = $queryBuilder->getQuery();
        $query->setResultCacheLifetime(60*60);
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('MIN(e.created) AS minDate')->from('addventure\Episode', 'e');
        foreach($user->getAuthorNames() as $a) {
            $queryBuilder->orWhere('e.author=' . $a->getId());
        }
        $res = $queryBuilder->getQuery()->getOneOrNullResult();
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('MAX(e.created) AS maxDate')->from('addventure\Episode', 'e');
        foreach($user->getAuthorNames() as $a) {
            $queryBuilder->orWhere('e.author=' . $a->getId());
        }
        $res = $queryBuilder->getQuery()->getOneOrNullResult();
        if($res != null) {
            return new \DateTime($res['maxDate']);
        }
        return null;
    }
    
    public function getWeeklyStat() {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('count', 'count', 'integer');
        $rsm->addScalarResult('date', 'date', 'string');
        $query = $this->getEntityManager()->createNativeQuery('SELECT COUNT(*) AS count, '
                . 'EXTRACT(YEAR FROM created) || \'-\' || EXTRACT(MONTH FROM MIN(created)) || \'-\' || EXTRACT(DAY FROM MIN(created)) AS date '
                . 'FROM Episode WHERE created IS NOT NULL '
                . 'GROUP BY EXTRACT(YEAR FROM created), EXTRACT(WEEK FROM created) '
                . 'ORDER BY EXTRACT(YEAR FROM created), EXTRACT(WEEK FROM created)', $rsm);
        $query->setResultCacheLifetime(60*60);
        $result = array();
        foreach($query->getArrayResult() as $entry) {
            $result[] = sprintf('[\'%s\', %d]', $entry['date'], $entry['count']);
        }
        return '[' . implode(', ', $result) . ']';
    }

}
