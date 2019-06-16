<?php

namespace App\Controller;

use App\Entity\City;
use App\Helpers\RequestHelper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

  class IndexController extends AbstractController
{
  /**
   * @Route("/", name="index", methods={"GET","POST"})
   */
    public function index(Request $request)
    {
        $city = new City();

        $form = $this->createFormBuilder($city)
            ->add('City', TextType::class,['label' => 'Cidade'])
            ->add('save', SubmitType::class, ['label' => 'Pesquisar'])
            ->getForm();

        $form->handleRequest($request);

        $arrayResponse = array(
            'form' => $form->createView(),
            'results' => array(),
            'error' => '',
            'temp' => '',
            'genre' => ''
        );

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            if (!$data->getCity())
              throw new \Exception("Digite a cidade novamente", 1);

            $requestHelper = new RequestHelper();

            $genre = $requestHelper->getTempGenreForCity($data->getCity());
            if (!$genre) {
              $arrayResponse['error'] = 'Cidade não encontrada';
            }else {
              $results = $requestHelper->getSongsForGenre($genre['genre']);
              $arrayResponse['results'] = $results;
              $arrayResponse['temp'] = $genre['temp'];
              $arrayResponse['genre'] = $genre['genre'];
            }
        }

        return $this->render('index/index.html.twig', $arrayResponse);
    }
}
