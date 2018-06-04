<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Entity\Employee;
use CalendarBundle\Entity\Entry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

/**
 * Entry controller.
 *
 * @Route("entry")
 */
class EntryController extends Controller
{
    /**
     * Lists all entry entities.
     *
     * @Route("/", name="entry_index")
     * @Method({"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function indexAction(Request $request)
    {
        $entry = null;
        $entries = null;
        $response = null;
        $repository =$this->getDoctrine()->getRepository('CalendarBundle:Entry');

        // If $request has full name of employee and/or period show filtred result
        if (!empty($request->get('name'))) {
            $employeeRepository =$this->getDoctrine()->getRepository('CalendarBundle:Employee');
            $employee = $employeeRepository->findOneBy(array('fullName' => $request->get('name')));

            if (empty($employee)) {
                return new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_ACCEPTABLE);
            }

            // Check interval between times for employee.
            if (!empty($request->get('from')) && !empty($request->get('to')) && !empty($employee)) {
                $dateTimeFrom = new \DateTime();
                $dateTimeFrom->setTimestamp($request->get('from'));

                $dateTimeTo = new \DateTime();
                $dateTimeTo->setTimestamp($request->get('to'));

                $timeTo = $dateTimeTo->format('Y-m-d H:i:sP');
                $timeFrom = $dateTimeFrom->format('Y-m-d H:i:sP');

                // Find all entries of employee between dates
                $entriesOfPeriod = $repository->findAllEntriesByDate($employee->getId(), $timeFrom, $timeTo);

                return new View($entriesOfPeriod, Response::HTTP_OK);
            }

            return new View($employee, Response::HTTP_OK);
        }


        // Show all results if $request doesn't contain name and period.
        $entries = $repository->findAll();
        $response = ($entries == null) ? new View(
            ["status" =>  "error", "message" => 'There are no entries exist'],
              Response::HTTP_NOT_FOUND
        ) : new View(
                  $entries,
              Response::HTTP_OK
              );

        return $response;
    }

    /**
     * Creates a new entry entity.
     *
     * @Route("/new", name="entry_new")
     * @Method({"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function newAction(Request $request)
    {
        $response = null;

        // Запись сделать пока можно по номеру врача
        $number = $request->get('number');
        if (empty($request->get('number'))) {
            $response = new View(["status" =>  "error", "message" => "Enter the employee's number"], Response::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        // Find employee
        $repository = $this->getDoctrine()->getRepository('CalendarBundle:Employee');
        $employee = $repository->findOneBy(array('number' => $request->get('number')));

        if (empty($employee)) {
            $response = new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        // Check interval between dates.
        $dateTimeFrom = new \DateTime();
        $dateTimeFrom->setTimestamp($request->get('from'));

        $dateTimeTo = new \DateTime();
        $dateTimeTo->setTimestamp($request->get('to'));

        $interval = $dateTimeFrom->diff($dateTimeTo);
        $intervalDays = $interval->format("%R%a");

        if ($intervalDays < 0) {
            $response = new View(["status" =>  "error", "message" => "Incorrect period of time"], Response::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        $timeTo = $dateTimeTo->format('Y-m-d H:i:sP');
        $timeFrom = $dateTimeFrom->format('Y-m-d H:i:sP');

        $dayFrom = $dateTimeFrom->format('Y-m-d');

        // Don't allow to create entry if the employee already has the sum of entries is 8 hours in the day.
        $entryRepository =$this->getDoctrine()->getRepository('CalendarBundle:Entry');

        $hoursOfDay = $entryRepository->findAllEntriesByEmployee($employee->getId(), $dayFrom);

        if (!empty($hoursOfDay) && $hoursOfDay >= 8) {
            $response = new View(["status" =>  "error", "message" => "The sum of entry of employee can't be more than 8 hours in this day"], Response::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        // Allowed statuses.
        $statuses =  ["holiday", "sick", "operating", "admission"];

        $status = $request->get('status');
        if (empty($request->get('status')) || !in_array($request->get('status'), $statuses)) {
            $response = new View(["status" =>  "error", "message" => "Incorrect entry status"], Response::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        $entry = new Entry();
        $entry->setDateFrom(new \DateTime($timeFrom));
        $entry->setDateTo(new \DateTime($timeTo));
        $entry->setStatus($status);
        // Relates this entry to the employee
        $entry->setEmployee($employee);

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $newEntryFull = $this->getDoctrine()->getRepository('CalendarBundle:Entry')->find($entry->getId());

        // Create new array, because we don't need to display all entries of current employee.
        $newEntry = ['id' => $newEntryFull->getId(), 'status' => $newEntryFull->getStatus(), 'employee' => $number, 'from' => $newEntryFull->getDateFrom(), 'to' => $newEntryFull->getDateTo()];
        $response = new View($newEntry, Response::HTTP_CREATED);
        return $response;
    }

    /**
     * Finds and displays a entry entity.
     *
     * @Route("/{id}", name="entry_show")
     * @Method("GET")
     * @param $id integer ID of entry
     * @return \FOS\RestBundle\View\View $response

     */
    public function showAction($id)
    {
        $response = null;
        $entry = $this->getDoctrine()->getRepository('CalendarBundle:Entry')->find($id);
        if ($entry == null) {
            $response = new View(["status" =>  "error", "message" => "Entry not found"], Response::HTTP_NOT_FOUND);
        } else {
            $response = new View($entry, Response::HTTP_OK);
        }
        return $response;
    }

    /**
     * Edit an existing entry entity.
     *
     * @Route("/{id}/edit", name="entry_edit")
     * @Method("PUT")
     * @param $id integer ID of entry
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function editAction(Request $request, $id)
    {
        $response = null;

        $em = $this->getDoctrine()->getManager();
        $entry = $this->getDoctrine()->getRepository('CalendarBundle:Entry')->find($id);
        if (empty($entry)) {
            return new View(["status" =>  "error", "message" => "Entry not found"], Response::HTTP_NOT_FOUND);
        }
        // Find employee
        if (!empty($request->get('number'))) {
            $repository = $this->getDoctrine()
                  ->getRepository('CalendarBundle:Employee');
            $employee = $repository->findOneBy(['number' => $request->get('number')]);

            if (empty($employee)) {
                $response = new View([
                      "status" => "error",
                      "message" => "Employee not found"
                    ], Response::HTTP_NOT_ACCEPTABLE);
                return $response;
            }
            $entry->setEmployee($employee);
        }

        if (!empty($request->get('status'))) {
            // Allowed statuses.
            $statuses = ["holiday", "sick", "operating", "admission"];

            if (!in_array($request->get('status'), $statuses)) {
                $response = new View([
                      "status" => "error",
                      "message" => "Incorrect entry status"
                    ], Response::HTTP_NOT_ACCEPTABLE);
                return $response;
            }

            $entry->setStatus($request->get('status'));
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new View(["status" =>  "error"], Response::HTTP_NOT_ACCEPTABLE);
        }
        $response = $this->getDoctrine()->getRepository('CalendarBundle:Entry')->find($id);

        return $response;
    }

    /**
     * Deletes a entry entity.
     *
     * @Route("/{id}", name="entry_delete")
     * @Method("DELETE")
     * @param $id integer ID of entry
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function deleteAction($id, Request $request)
    {
        //curl -H "Content-Type: application/json" -X DELETE http://127.0.0.1:8000/entry/50
        $response = null;
        $em = $this->getDoctrine()->getManager();
        $entry = $this->getDoctrine()->getRepository('CalendarBundle:Entry')->find($id);
        if (empty($entry)) {
            $response = new View(["status" =>  "error", "message" => "Entry not found"], Response::HTTP_NOT_FOUND);
        } else {
            $em->remove($entry);
            $em->flush();
            $response = new View(["status" => "success", "message" => "Entry deleted successfully"], Response::HTTP_OK);
        }

        return $response;
    }
}
