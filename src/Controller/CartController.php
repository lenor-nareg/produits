<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(SessionInterface $session, ProduitRepository $repo): Response
    {
        $cart = $session->get('cart', []);
        //je recup le tableau stocké ds la session s'il existe, ou un tableau vide s'il n'existe pas 

        $cartWithData = [];
        //ce tableau $cartWithData[] va contenir les produits au lieu des id des produits

        foreach($cart as $id => $quantity)//parcourir le panier 
        {
            $cartWithData[] = [
                'produit' => $repo->find($id),
                'quantity' => $quantity
            ];
            //pr chaq id de produit stocké ds le panier, je recup le produit associé et le stocke ds mon $cartWithData[] avec la quantité
        }

        $total = 0;// cette var va contenir le total de mon panier

        foreach($cartWithData as $item)
        {
            $totalItem = $item['produit']->getPrix() * $item['quantity'];
            // $totalItem contient le prix total pr chaq prod en fonction de sa quantité
            // $item['product'] contient un objet Product, nous avons dc accès aux methodes de cette objet : nous utilisons getPrice() qui nous renvoie le prix de ce produit puis nous multiplions le prix du prod par sa quantité
            $total += $totalItem;
            //equivalent à $total = =total + $totalItem;
            //j'ajoute le total par item au total final 

        }

        // dd($cartWithData);

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }
    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add($id, SessionInterface $session): Response
    {//l'objet $session represente la session de l'utilisateur
        $cart = $session->get('cart', []);//on veut recup la variable de session "cart", ou un tableau vide pr defaut si la variable "cart" n'existe pas 

        if(!empty($cart[$id])){$cart[$id]++;}
        else{$cart[$id] = 1;}
        

        $session->set('cart', $cart);//j'indique que ma var de session "cart" doit contenir les datas du tableau $cart

        // dd($session->get('cart'));//dd() : dump & die : methode predefinie permettant de dump le contenu d'une variable puis de stopper l'execution du code 

        // return $this->render('cart/index.html.twig', [
        //     'controller_name' => 'CartController',
        // ]);
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete")
    */
    public function delete($id, SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        if(!empty($cart[$id]))//si l'id du prod est stocké ds mon panier 
        {
            unset($cart[$id]);//je supp cet id de mon panier 
        }
        $session->set('cart', $cart); //je sauvegarde l'etat de mon panier ds la session 
        return $this->redirectToRoute('cart');
    }
    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement")
    */
    public function decrement($id, SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        if(!empty($cart[$id]))//si j'ai bien le produit ds mon panier
        {
            if($cart[$id] > 1)//si on a 1 qté du prod > à 1, je retire 1
                $cart[$id]--;
            else //snn j'ai 1 fois le prod et je dois passer à 0 après la decrementation je le supp
                unset($cart[$id]);
        
        }

        $session->set('cart', $cart); //je sauvegarde l'etat de mon panier ds la session 
        return $this->redirectToRoute('cart');
    }
}
