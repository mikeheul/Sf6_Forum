<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-administrator',
    description: 'Create an administrator',
)]
class CreateAdministratorCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) 
    {
        parent::__construct("app:create-administrator");
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pseudo', InputArgument::OPTIONAL, 'Pseudo')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);
        $pseudo = $input->getArgument('pseudo');
        if(!$pseudo) {
            $question = new Question("Admin pseudo : ");
            $pseudo = $helper->ask($input, $output, $question);
        }

        $email = $input->getArgument('email');
        if(!$email) {
            $question = new Question("Admin email : ");
            $email = $helper->ask($input, $output, $question);
        }
        
        $password = $input->getArgument('password');
        if(!$password) {
            $question = new Question("Admin password : ");
            $password = $helper->ask($input, $output, $question);
        }

        $user = (new User())->setPseudo($pseudo)
            ->setEmail($email)
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setRoles(["ROLE_USER", "ROLE_ADMIN"]);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('New admin created successfully !');

        return Command::SUCCESS;
    }
}
