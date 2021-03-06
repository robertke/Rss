<?php

namespace Rss\Src;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Rss\Src\RssDomDocumentIterator;

class RssCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('grab')
            ->setDescription('Tool to grab rss feeds to DB.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the url to grab f.g http://www.nfq.lt/rss: ');
        $question->setValidator(function ($answer) {

            if (trim($answer) == '') {
                throw new \Exception('The url can not be empty');
            }

            if ('http://' !== substr(trim($answer), 0, 7)) {
                throw new \RuntimeException(
                    'The name of the url should be started with \'http://\''
                );
            }

//            if (!preg_match('/^(http):\\/\\/[a-z0-9_.]{1,255}$/', $answer, $matches)) {
//                throw new \RuntimeException('Not valid. Url max length is limited to 255 symbols.');
//            }

            return $answer;
        });
        $question->setMaxAttempts(3);

        $answer_url = $helper->ask($input, $output, $question);

        $question2 = new Question('Please enter the category for rss feed: ');
        $question2->setValidator(function ($answer) {
            if (!preg_match('/^[a-zA-Z_]{1,255}$/', $answer, $matches)) {
                throw new \RuntimeException('Only letters are allowed. Category name max length is limited to 255 symbols.');
            }

            return $answer;
        });
        $question2->setMaxAttempts(3);

        $answer_cat = $helper->ask($input, $output, $question2);


        if (($answer_url) && ($answer_cat)) {

            $rss_service = new RssService(new RssReader($answer_url, $answer_cat));
            $rss_service->read();
            $rss_service->persist(new RssEntityManager(new Config()));


            $text = "You entered: $answer_url and $answer_cat. ";
            $text .= "Rss feed and items have been imported to DB successfully";
        } else {
            $text = 'Nothing entered. Try once again.';
        }

        $output->writeln($text);
    }
}