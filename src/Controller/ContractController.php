<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use App\Service\YousignService;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/contract')]
class ContractController extends AbstractController
{
    #[Route('/', name: 'app_contract_index', methods: ['GET'])]
    public function index(ContractRepository $contractRepository): Response
    {
        return $this->render('contract/index.html.twig', [
            'contracts' => $contractRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contract_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContractRepository $contractRepository): Response
    {
        $contract = new Contract();
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contractRepository->save($contract, true);

            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contract/new.html.twig', [
            'contract' => $contract,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show', methods: ['GET'])]
    public function show(Contract $contract): Response
    {
        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contract $contract, ContractRepository $contractRepository): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contractRepository->save($contract, true);

            return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contract/edit.html.twig', [
            'contract' => $contract,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_contract_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract, ContractRepository $contractRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contract->getId(), $request->request->get('_token'))) {
            $contractRepository->remove($contract, true);
        }

        return $this->redirectToRoute('app_contract_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_contract_pdf', methods: ['GET'])]
    public function pdf(
        Contract $contract,
        ContractRepository $contractRepository,
        Filesystem $filesystem
    ): Response {
        $dompdf = new Dompdf();

        $html = $this->render('contract/pdf.html.twig', [
            'contract' => $contract
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'contract_' . $contract->getId() . '.pdf';
        $path = $this->getParameter('kernel.project_dir') . '/var/storage/';
        $file = $path . $filename;

        $contract->setOriginalPdfFilename($filename);
        $contractRepository->save($contract, true);

        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
        }

        $filesystem->dumpFile($file, $output);

        return $this->redirectToRoute('app_contract_show', [
            'id' => $contract->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}/signature', name: 'app_contract_signature')]
    public function signature(
        Contract $contract,
        ContractRepository $contractRepository,
        YousignService $yousignService
    ): Response {
        $yousignSignatureRequest = $yousignService->signatureRequest();
        $contract->setYousignSignatureId($yousignSignatureRequest['id']);

        $uploadDocument = $yousignService->uploadDocument(
            $contract->getYousignSignatureId(),
            $contract->getOriginalPdfFilename()
        );
        $contract->setYousignDocumentId($uploadDocument['id']);

        $signerId = $yousignService->addSigner(
            $contract->getYousignSignatureId(),
            $contract->getYousignDocumentId(),
            $contract->getEmail(),
            $contract->getFirstname(),
            $contract->getLastname()
        );
        $contract->setYousignSignerId($signerId['id']);

        $contractRepository->save($contract, true);

        $yousignService->activateSignatureRequest($contract->getYousignSignatureId());

        $followers = explode(',', $this->getParameter('yousign_followers_email'));
        foreach ($followers as $follower) {
            $yousignService->addFollower($follower, $yousignSignatureRequest['id']);
        }

        return $this->redirectToRoute('app_contract_show', ['id' => $contract->getId()]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/webhook', methods: ['POST'], priority: 2)]
    public function webhook(
        Request $request,
        ContractRepository $contractRepository,
        Filesystem $filesystem,
        YousignService $yousignService
    ): JsonResponse {
        $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        if ($data->event_name === 'signature_request.done') {
            $contract = $contractRepository->findOneBy(['yousignSignatureId' => $data->data->signature_request->id]);

            if (!$contract) {
                return $this->json(['error' => 'Unable to find contract'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $contract->setIsSigned(true);
            $contractRepository->save($contract, true);

            $filename = 'signed_contract_' . $contract->getId() . '.pdf';
            $path = $this->getParameter('kernel.project_dir') . '/var/storage/';
            $file = $path . $filename;

            $filesystem->dumpFile(
                $file,
                $yousignService->downloadDocuments($data->data->signature_request->id)
            );
        }

        return $this->json(['success' => true], Response::HTTP_OK);
    }
}
