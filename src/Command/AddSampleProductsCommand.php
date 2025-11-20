<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-sample-products',
    description: 'Add sample products to the database',
)]
class AddSampleProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $products = [
            [
                'nom' => 'Jus de Fraise',
                'description' => 'Délicieux jus de fraise 100% naturel, riche en vitamine C et antioxydants. Parfait pour un petit-déjeuner énergisant.',
                'price' => 4.50,
            ],
            [
                'nom' => 'Smoothie Tropical',
                'description' => 'Mélange exotique de mangue, ananas et banane. Un véritable voyage gustatif dans les tropiques.',
                'price' => 5.90,
            ],
            [
                'nom' => 'Jus d\'Orange Pressé',
                'description' => 'Jus d\'orange fraîchement pressé, source naturelle de vitamine C. Idéal pour commencer la journée.',
                'price' => 3.50,
            ],
            [
                'nom' => 'Smoothie Vert Détox',
                'description' => 'Mélange de légumes verts, épinards, concombre et pomme verte. Parfait pour une cure détox.',
                'price' => 6.50,
            ],
            [
                'nom' => 'Jus de Pomme',
                'description' => 'Jus de pomme 100% pur, sans sucre ajouté. Doux et rafraîchissant.',
                'price' => 3.00,
            ],
            [
                'nom' => 'Smoothie Baies Rouges',
                'description' => 'Mélange de fraises, framboises, myrtilles et mûres. Riche en antioxydants et en saveurs.',
                'price' => 5.50,
            ],
        ];

        $io->progressStart(count($products));

        foreach ($products as $productData) {
            $product = new Product();
            $product->setNom($productData['nom']);
            $product->setDescription($productData['description']);
            $product->setPrice((string)$productData['price']);

            $this->entityManager->persist($product);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf('Successfully added %d sample products!', count($products)));
        $io->note('Visit http://localhost:8000 to see the products');

        return Command::SUCCESS;
    }
}

