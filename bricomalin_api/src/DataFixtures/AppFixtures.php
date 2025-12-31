<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\JobRequest;
use App\Entity\Offer;
use App\Entity\ProfessionalProfile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Catégories
        $categories = [
            ['name' => 'Plomberie', 'key' => 'plomberie'],
            ['name' => 'Électricité', 'key' => 'electricite'],
            ['name' => 'Peinture', 'key' => 'peinture'],
            ['name' => 'Montage de meubles', 'key' => 'montage-meubles'],
            ['name' => 'Jardinage', 'key' => 'jardinage'],
            ['name' => 'Petits travaux', 'key' => 'petits-travaux'],
        ];

        $categoryEntities = [];
        foreach ($categories as $catData) {
            $category = new Category();
            $category->setName($catData['name']);
            $category->setKey($catData['key']);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Utilisateurs
        $user1 = new User();
        $user1->setEmail('user@example.com');
        $user1->setDisplayName('Jean Dupont');
        $user1->setPhone('0612345678');
        $user1->setPasswordHash($this->passwordHasher->hashPassword($user1, 'password123'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('pro@example.com');
        $user2->setDisplayName('Marie Martin');
        $user2->setPhone('0698765432');
        $user2->setPasswordHash($this->passwordHasher->hashPassword($user2, 'password123'));
        $manager->persist($user2);

        // Profil PRO pour user2
        $proProfile = new ProfessionalProfile();
        $proProfile->setUser($user2);
        $proProfile->setSiret('12345678901234');
        $proProfile->setStatus(ProfessionalProfile::STATUS_VERIFIED);
        $manager->persist($proProfile);

        // Demandes
        $jobRequest1 = new JobRequest();
        $jobRequest1->setRequester($user1);
        $jobRequest1->setCategory($categoryEntities[0]); // Plomberie
        $jobRequest1->setTitle('Réparation robinet qui fuit');
        $jobRequest1->setDescription('Mon robinet de cuisine fuit depuis plusieurs jours. J\'ai besoin d\'un plombier pour le réparer rapidement.');
        $jobRequest1->setDepartment('75');
        $jobRequest1->setCity('Paris');
        $jobRequest1->setIsFree(false);
        $jobRequest1->setSuggestedPrice('80.00');
        $jobRequest1->setStatus(JobRequest::STATUS_OPEN);
        $manager->persist($jobRequest1);

        $jobRequest2 = new JobRequest();
        $jobRequest2->setRequester($user1);
        $jobRequest2->setCategory($categoryEntities[2]); // Peinture
        $jobRequest2->setTitle('Peinture salon 25m²');
        $jobRequest2->setDescription('Je souhaite repeindre mon salon. Les murs sont déjà préparés, il faut juste appliquer la peinture.');
        $jobRequest2->setDepartment('92');
        $jobRequest2->setCity('Nanterre');
        $jobRequest2->setIsFree(false);
        $jobRequest2->setSuggestedPrice('200.00');
        $jobRequest2->setStatus(JobRequest::STATUS_OPEN);
        $manager->persist($jobRequest2);

        // Offre
        $offer1 = new Offer();
        $offer1->setJobRequest($jobRequest1);
        $offer1->setProposer($user2);
        $offer1->setAmount('75.00');
        $offer1->setMessage('Bonjour, je peux intervenir demain matin. Je propose 75€ pour cette réparation.');
        $offer1->setStatus(Offer::STATUS_PENDING);
        $manager->persist($offer1);

        $manager->flush();
    }
}

