<?php

namespace App\Service;

use App\Configuration\PaymentConfig;
use App\Entity\Application;
use App\Entity\Licence;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentService
{

    public function __construct(
        private PaymentRepository $paymentRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private ApplicationRepository $applicationRepository
    )
    {
    }

    public function createPayment(Application $application, User $user): Payment
    {
        $payment = new Payment();
        $payment->setUserId($user->getId());
        $payment->setAppName($application->getId());
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setStatus(0);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $payment;
    }

    public function generatePayment(Application $application, User $user) : array {
        $payment = $this->createPayment($application, $user);

        $form = [
            'SEKRET' => PaymentConfig::SECRET,
            'KWOTA' => $application->getCost(),
            'NAZWA_USLUGI' => $application->getName(),
            'ADRES_WWW' => PaymentConfig::WWW,
            'ID_ZAMOWIENIA' => $payment->getId(),
            'EMAIL' => $user->getEmail(),
            'DANE_OSOBOWE' => ''
        ];

        return [
            'payment' => $payment,
            'form' => $form
        ];
    }

    public function result(Request $request) : int
    {
        $KWOTA = $request->request->get('KWOTA');
        $ID_PLATNOSCI = $request->request->get('ID_PLATNOSCI');
        $ID_ZAMOWIENIA = $request->request->get('ID_ZAMOWIENIA');
        $STATUS = $request->request->get('STATUS');
        $SEKRET = $request->request->get('SEKRET');
        $SECURE = $request->request->get('SECURE');
        $HASH = $request->request->get('HASH');

        if ($request->isMethod('POST')) {
            if (!empty($KWOTA) && !empty($ID_PLATNOSCI) && !empty($ID_ZAMOWIENIA) && !empty($STATUS) && !empty($SEKRET) && !empty($SECURE) && !empty($HASH)) {
                $hash = hash("sha256", PaymentConfig::PASSWORD.";" . $KWOTA . ";" . $ID_PLATNOSCI . ";" . $ID_ZAMOWIENIA . ";" . $STATUS . ";" . $SECURE . ";" . $SEKRET);

                $payment = $this->paymentRepository->find($ID_ZAMOWIENIA);

                if (!$payment) {
                    return 0;
                }
                if ($hash == $HASH) {
                    if ($STATUS == "SUCCESS") {
                        $payment->setStatus(1);
                        $this->entityManager->flush();
                        return 1;
                    } else if ($STATUS == "PENDING") {
                        $payment->setStatus(0);
                        $this->entityManager->flush();
                        return 2;
                    } else if ($STATUS == 'FAILURE') {
                        $payment->setStatus(2);
                        $this->entityManager->flush();
                        return 3;
                    }
                } else {
                    return 4;
                }
            } else {
                return 5;
            }
        } else {
            return 6;
        }
        return 0;
    }

    public function tryGiveLicence(int $ID_ZAMOWIENIA) : void
    {
        if (!$this->giveLicence($ID_ZAMOWIENIA)) {
            $this->logger->error('====');
            $this->logger->error('Failed to give license for user, payment id: ' . $ID_ZAMOWIENIA);
            $this->logger->error('====');
        }
    }

    public function giveLicence(int $ID_ZAMOWIENIA) : bool
    {
        $payment = $this->paymentRepository->find($ID_ZAMOWIENIA);
        if (!$payment) {
            return false;
        }

        $user = $this->userRepository->find($payment->getUserId());
        if (!$user) {
            return false;
        }

        $app = $this->applicationRepository->find($payment->getAppName());
        if (!$app) {
            return false;
        }

        $licence = new Licence();
        $licence->setCreatedAt(new \DateTimeImmutable());
        $licence->setUser($user);
        $licence->setApplication($app);
        $licence->setAdmin(null);
        $licence->setLicenceKey(bin2hex(random_bytes(32)));
        $licence->setRequireHost(true);
        $licence->setNote('Buy via system');

        $this->entityManager->persist($licence);
        $this->entityManager->flush();

        return true;
    }


}