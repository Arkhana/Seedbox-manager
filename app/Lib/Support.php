<?php

namespace app\Lib;

Class Support extends Users {

    public function ReadTicket()
    {
        foreach ( $this->TicketList() as $encoded_ticket )
        {
            $ticket[] = $this->DecodeTicket($encoded_ticket);
            // indiquer ici si le ticket et fermé
            // avec cette méthode 
        }
        if (empty($ticket))
            $ticket = 'Aucun ticket';

        return $ticket;
    }

    /*
        Cette méthode crée les tickets pour les utilisateurs.
        Elle permet au admin de répondre dans les tickets

        Crée un fichier support.json dans le dossier conf/ du user qui crée le topic
    */

    public function sendTicket( $message, $destinataire)
    {
        $date = date("d/m/y \à H\hi");

        if (file_exists('./../conf/users/'.$name.'/support.json'))
        {
            $open_ticket = './../conf/users/'.$name.'/support.json';
            $ticket = json_decode(file_get_contents($open_ticket));
            $ticket[] = array( 'data' => array( 'user' => $this->userName, 'date' => $date, 'message' => $message));  
        }
        else
            $ticket = array( 'data' => array( 'user' => $this->userName, 'date' => $date, 'message' => $message));

        $encoded_ticket = json_encode($ticket);
        $log_write_ticket = @file_put_contents('./../conf/users/'.$destinataire.'/support.json', $encoded_ticket.PHP_EOL);

        if ($log_write_ticket === false)
            return $log_write_ticket;
        else
            return $log_write_ticket = true;
    }

    /*
        Cette méthode renvoie deux listes des fichiers tickets.
            Une liste pour l'admin.
            OU une liste pour le user.
        La liste des fichiers est sous forme d'array.
    */

    private function TicketList()
    {
        if ($this->is_owner === true)
        {
            $all_users = $this->get_all_users();
            foreach ( $all_users as $user )
            {
                if ( $user != $this->userName)
                    $files_ticket[] = glob('./../conf/users/'.$user.'/support*.json');
            }

            //converti un tableau multidimensionnel en un tableau unidimensionnel.
            array_walk_recursive( $files_ticket,
                function( $a, $b) use (&$all_files_tickets)
                {
                    $all_files_tickets[] = $a;
                });

            return $all_files_tickets;
        }
        else
        {
            $files_tickets = glob('./../conf/users/'.$this->userName.'/support*.json');
            return $files_tickets;
        }
    }

    /*
        Methode cloture :
        Cherche tous les fichiers avec l'extension avec le pattern support*.json puis les comptes
        Renomme le fichier support.json (dernier ticket) en support_X.json
    */

    public function cloture($user)
    {
        $scan_ticket = glob('./../conf/users/'.$user.'/support*.json');
        $nb_ticket = count($scan_ticket);

        return rename('./../conf/users/'.$user.'/support.json', './../conf/users/'.$user.'/support_'.$nb_ticket.'.json' );
    }

    /* retourne si un ticket est fermé ou non */

    public function EtatTicket($file_ticket)
    {
        $log = stripos($file_ticket, 'support_');
        return $log;
    }

    /* Méthode qui décode le ticket */

    private function DecodeTicket($ticket)
    {
        $json = json_decode(file_get_contents($ticket), true);
        return $json;
    }
}