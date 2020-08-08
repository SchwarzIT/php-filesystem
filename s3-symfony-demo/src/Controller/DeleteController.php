<?php

namespace App\Controller;

use Chapterphp\FileSystem\FileSystemInterface;
use Chapterphp\FileSystem\Model\FileName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteController extends AbstractController
{
    /**
     * @Route("/delete/{file_name}", name="delete", methods={"DELETE"})
     */
    public function __invoke(Request $request, FileSystemInterface $s3FileSystem): Response
    {
        $fileName = $request->get('file_name');
        $s3FileSystem->delete(FileName::create($fileName));

        return new Response(200);
    }
}
