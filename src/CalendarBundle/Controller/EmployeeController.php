<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

/**
 * Employee controller.
 *
 * @Route("employee")
 */
class EmployeeController extends FOSRestController
{
    /**
     * Lists all employee entities.
     *
     * @Route("/", name="employee_index")
     * @Method({"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function indexAction(Request $request)
    {
        $response = null;
        $repository =$this->getDoctrine()->getRepository('CalendarBundle:Employee');

        // If $request contains number or name return related employee.
        if (!empty($request->get('number'))) {
            $employee = $repository->findOneBy(array('number' => $request->get('number')));
            $response = empty($employee) ? new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_ACCEPTABLE) : new View($employee, Response::HTTP_OK);
        } elseif (!empty($request->get('name'))) {
            $employee = $repository->findOneBy(array('fullName' => $request->get('name')));
            $response = empty($employee) ? new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_ACCEPTABLE) : new View($employee, Response::HTTP_OK);
        } else {
            // If $request doesn't contains number or name return all employees.
            $employees = $repository->findAll();
            $response = ($employees == null) ? new View(
                ['error' => 'There are no employees exist'],
              Response::HTTP_NOT_FOUND
            ) : new View(
                  $employees,
              Response::HTTP_OK
              );
        }

        return $response;
    }

    /**
     * Creates a new employee entity.
     *
     * @Route("/new", name="employee_new", defaults={"id" = 0})
     * @Method({"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function newAction(Request $request)
    {
        $response = null;
        $employee = new Employee();
        $name = $request->get('name');
        $number = $request->get('number');
        $note = $request->get('note');
        if (empty($name) || empty($number)) {
            $response = new View(["status" =>  "error", "message" => "NULL values are not allowed"], Response::HTTP_NOT_ACCEPTABLE);
        }

        $employee->setFullName($name);
        $employee->setNumber($number);
        $employee->setNote($note);
        $em = $this->getDoctrine()->getManager();
        $em->persist($employee);
        $em->flush();

        $newEmployee = $this->getDoctrine()->getRepository('CalendarBundle:Employee')->find($employee->getId());
        $response = new View($newEmployee, Response::HTTP_CREATED);
        return $response;
    }

    /**
     * Finds and displays a employee entity.
     *
     * @Route("/{id}", name="employee_show")
     * @Method("GET")
     * @param $id integer ID of employee
     * @return \FOS\RestBundle\View\View $response
     */
    public function showAction($id)
    {
        $response = null;
        $employee = $this->getDoctrine()->getRepository('CalendarBundle:Employee')->find($id);
        if ($employee == null) {
            $response = new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_FOUND);
        } else {
            $response = new View($employee, Response::HTTP_OK);
        }
        return $response;
    }

    /**
     * Edit an existing employee entity.
     *
     * @Route("/{id}/edit", name="employee_edit")
     * @Method("PUT")
     * @param $id integer ID of employee
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function editAction($id, Request $request)
    {
        // curl -d '{"name":"333i", "note":"JJJFJJFJFF"}'  -H "Content-Type: application/json" -X PUT http://127.0.0.1:8000/employee/13/edit
        $response = null;
        $name = $request->get('name');
        $note = $request->get('note');
        $em = $this->getDoctrine()->getManager();
        $employee = $this->getDoctrine()->getRepository('CalendarBundle:Employee')->find($id);
        if (empty($employee)) {
            $response = new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_FOUND);
        } else {
            if (!empty($name)) {
                $employee->setFullName($name);
            }
            if (!empty($note)) {
                $employee->setNote($note);
            }
            $em->flush();
            $response = $this->getDoctrine()->getRepository('CalendarBundle:Employee')->find($id);
        }
        return $response;
    }

    /**
     * Deletes a employee entity.
     *
     * @Route("/{id}", name="employee_delete")
     * @Method("DELETE")
     * @param $id integer ID of employee
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \FOS\RestBundle\View\View $response
     */
    public function deleteAction($id, Request $request)
    {
        $response = null;
        $em = $this->getDoctrine()->getManager();
        $employee = $this->getDoctrine()->getRepository('CalendarBundle:Employee')->find($id);
        if (empty($employee)) {
            $response = new View(["status" =>  "error", "message" => "Employee not found"], Response::HTTP_NOT_FOUND);
        } else {
            $em->remove($employee);
            $em->flush();
            $response = new View(["status" => "success", "message" => "Employee deleted successfully"], Response::HTTP_OK);
        }
        return $response;
    }
}
