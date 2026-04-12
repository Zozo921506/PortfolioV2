<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    //    /**
    //     * @return Message[] Returns an array of Message objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findByProjectId(int $projectId): array
    {
        $connect = $this->getEntityManager()->getConnection();
        $query = "SELECT message.id, user.firstname, user.lastname, message.text FROM message LEFT OUTER JOIN user ON user.id = message.id_user WHERE message.id_project = ?";
        $prepare = $connect->prepare($query);
        $result = $prepare->executeQuery([$projectId]);
        return $result->fetchAllAssociative();
    }
}
