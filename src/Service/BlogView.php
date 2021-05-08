<?php


namespace App\Service;


use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BlogView
 * @package App\Service
 */
class BlogView
{


    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(PaginatorInterface $paginator, RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getCategory($category){
        return $this->em
            ->getRepository(Category::class)
            ->findOneBy(
                [
                    'wording' => $category
                ]
            );
    }
    /**
     * @param $category
     * @return PaginationInterface
     */
    public function getArticlesByCategory($category, $page){
        $articles = $this->em
            ->getRepository(Article::class)
            ->findBy(
                [
                    'isOnline' => 1,
                    'category' => $this->getCategory($category)
                ],
                ['createdAt' => "DESC"],
                );
        $request = $this->requestStack->getCurrentRequest();
        return $this->paginator->paginate(
            $articles,
            $request->query->getInt('page', $page),
            9
        );
    }
}