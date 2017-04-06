<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Interaction;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        /*
         * To start ElasticSearch, run
         *
         * sudo service elasticsearch start
         *
         * in a terminal anywhere.
         *
         *
         * To access the SQL data from a web browser, open
         *
         * http://localhost/phpmyadmin/
         *
         * .
         *
         *
         * To save data from the MySQL server into elasticsearch, run
         *
         * app/console fos:elastica:populate
         *
         * in the working directory in a terminal.
         *
         * Look into the DefaultController.php and Interaction.php
         * (for DB schema) and config.yml ( for all configurations
         * .eg Elastic search schema)
         *
         */


        // Access MySQL
        $em = $this->getDoctrine()->getManager();
        $interactionRepository = $em->getRepository(Interaction::class);
        $allInteractions = $interactionRepository->findAll();

        $form = $this->createFormBuilder()
            ->add('searchTerm', TextType::class)
            //->add('dueDate', DateType::class)
            ->add('save', SubmitType::class, array('label' => 'Search'))
            ->getForm();



        // Access ElasticSearch
        /** @var TransformedFinder $finder */
        $finder = $this->container->get('fos_elastica.finder.app.interaction');


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $searchTermData = $form->getData();
            $searchTerm = $searchTermData['searchTerm'];


            $interactionPaginator = $finder->findPaginated($searchTerm);
        } else {
            $interactionPaginator = $finder->findPaginated("");
        }


        $interactionCountOfResults = $interactionPaginator->getNbResults();

        // replace this example code with whatever you need
        return $this->render(
            'default/index.html.twig',
            array(
                'base_dir' => "Something else...",
                'form' => $form->createView(),
                'interactions' => $allInteractions,
                'interactionPaginator' => $interactionPaginator,

            )
        );
    }
}
