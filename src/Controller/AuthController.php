<?php


namespace App\Controller;


use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $em,  UserPasswordEncoderInterface $userPasswordEncoder, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/login", methods={"POST"}, name="api_login")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        // Validate that 'email' and 'password' are sent
        if ($request->request->has('email') && $request->request->has('password')) {
            // Find user by email address
            $user = $this->userRepository->findOneBy(['email' => $request->request->get('email')]);

            // Validate email address
            if (!$user) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Wrong user email'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Validate password
            if (!$this->userPasswordEncoder->isPasswordValid($user, $request->request->get('password'))) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Wrong user password'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return new JsonResponse([
                'status'     => true,
                'auth_token' => 'Bearer '. $user->getApiToken()->getToken()
            ], Response::HTTP_OK);

        } else {
            return new JsonResponse([
                'status'  => false,
                'message' => 'Wrong user email or password'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}