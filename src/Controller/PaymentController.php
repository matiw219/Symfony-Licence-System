<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use App\Repository\PaymentRepository;
use App\Service\PaymentService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractDashboardController
{

    public function __construct(
        private ApplicationRepository $applicationRepository,
        private PaymentService $paymentService,
        private PaymentRepository $paymentRepository,

    )
    {}

    #[Route('/payment/processed', name: 'app_payment_processed')]
    public function paymentProcessed(Request $request) : Response
    {
        $code = $this->paymentService->result($request);
        $message = '';
        if ($code == 1) {
            $message = 'Payment has been processed correctly.';
        }
        else if ($code == 2) {
            $message = 'Payment is being processed.';
        }
        else if ($code == 3) {
            $message = 'Payment was rejected.';
        }
        else {
            $ID_ZAMOWIENIA = $request->request->get('ID_ZAMOWIENIA');
            $message = 'An unexpected error occurred during payment: x' . $code . ' #' . $ID_ZAMOWIENIA;
        }
        return new Response($message);
    }

    #[Route('/pay/{id}', name: 'app_payment')]
    public function payment(int $id) : Response
    {
        $app = $this->applicationRepository->find($id);

        if (!$app) {
            $this->addFlash('error', 'Nie odnaleziono takiej aplikacji!');
            return $this->redirectToRoute('app_shop');
        }

        $data = $this->paymentService->generatePayment($app, $this->getUser());

        return $this->render('hub/payment.html.twig', [
            'form' => $data['form']
        ]);
    }



}