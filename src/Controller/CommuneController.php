<?php


namespace App\Controller;


use App\Entity\Communes;
use App\Entity\Media;
use App\Repository\CommunesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommuneController extends AbstractController
{
    public function index(CommunesRepository $communesRepository)
    {
        return $this->render('', [
            'controller_name' => 'PresentationController',
            'communes' => $communesRepository->findAll()]);
    }

    /**
     * @Route("/communes/{slug}", name="getCommunesBySlug")
     * @param string $slug
     * @param CommunesRepository $communesRepository
     * @return Response
     */
    public function getCommuneBySlug(string $slug, CommunesRepository $communesRepository)
    {
        return $this->render('communes/login.html.twig', [
            'controller_name' => 'PresentationController',
            'communes' => $communesRepository->findBy(['slug' => $slug])
        ]);
    }


    /**
     * @Route("/communes/json/{slug}", name="jsonCommunesBySlug")
     * @param Communes $communes
     * @return JsonResponse
     */
    public function jsonGetCommunesBySlug(Communes $communes)
    {
        return JsonResponse::fromJsonString($this->serializeDepartement($communes));
    }

    /**
     * @Route("/communes/json/numero/{numero}", name="jsonGetCommuneByNumero")
     * @param Communes $communes
     * @return JsonResponse
     */
    public function jsonGetCommuneByNumero(Communes $communes)
    {
        return JsonResponse::fromJsonString($this->serializeDepartement($communes));
    }

    /**
     * @Route("/api/v2/json/communes", name="jsonv2Communes", methods={"GET"} )
     * @param Request $request
     * @param CommunesRepository $communesRepository
     * @return JsonResponse
     */
    public function jsonv2Communes(Request $request, CommunesRepository $communesRepository)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metaData = $em->getClassMetadata(Communes::class)->getFieldNames();
        foreach ($metaData as $value) {
            if ($request->query->get($value)) {
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeDepartement($communesRepository->findBy($filter)));
    }

    /**
     * @Route("/v2/json/communes", name="communes_create", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function communesCreate(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $datas = json_decode($request->getContent(), true);
        $communes = new Communes();
        $communes
            ->setNom($datas['nom'])
            ->setCode($datas['code'])
            ->setCodeDepartement($datas['codeDepartement'])
            ->setCodePostal($datas['codePostal'])
            ->setCodeRegion($datas['codeRegion']);

        if ($request->request->get('media')) {
            $array = $request->request->get('media');
            for ($i = 0; $i < count($array); $i++) {
                $media = new Media();
                $media->setIdCommune($communes)->setImage($array['url']);
                $entityManager->persist($media);
            }
        }
        $entityManager->persist($communes);
        $entityManager->flush();

        $response = new Response();
        $response->setContent("Commune créée avec l'id  " . $communes->getId());
        return $response;
    }

    /**
     * @Route("/v2/json/communes/patch", name="communeUpdate", methods={"PATCH"})
     * @param Request $request
     * @param CommunesRepository $communesRepository
     * @return Response
     */
    public function departmentUpdate(Request $request, CommunesRepository $communesRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $response = new Response();
        if (isset($data['commune_id']) && isset($data['nom'])) {
            $id = $data['commune_id'];
            $communes = $communesRepository->find($id);
            if ($communes === null) {
                $response->setContent("Cette commune n'existe pas");
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            } else {
                $communes->setNom($data['name']);
                $entityManager->persist($communes);
                $entityManager->flush();
                $response->setContent("Modification d'une commune");
                $response->setStatusCode(Response::HTTP_OK);
            }
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * @Route("/v2/json/departments/delete", name="departmentDelete", methods={"DELETE"})
     * @param Request $request
     * @param CommunesRepository $communesRepository
     * @return Response
     */
    public function communeDelete(Request $request, CommunesRepository $communesRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $response = new Response();
        if (isset($data['commune_id'])) {
            $id = $data['commune_id'];
            $commune = $communesRepository->find($id);
            if ($commune === null) {
                $response->setContent("Cette commune n'existe pas");
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            } else {
                $entityManager->remove($commune);
                $entityManager->flush();
                $response->setContent("Suppression de la commune");
                $response->setStatusCode(Response::HTTP_OK);
            }
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    private function serializeCommune($objet)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getSlug();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
}
