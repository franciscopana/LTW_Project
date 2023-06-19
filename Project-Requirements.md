# LTW Project

## GOAL
    Streamline and manage trouble tickets
    Users to submit, track, and resolve tickets

**SQLite** database that stores information about 
- users
- tickets
- departments
- hashtags
- frequently asked question(FAQ)

**HTML** and **CSS** web pages.
Use **PHP** to generate those web pages.
Use **Javascript** to enhance the user experience (for example, using Ajax).

Types of users:
- **clients** that want to submit and track new tickets
- **agents** that get assigned to tickets and solve them
- **admins** that have complete control over the website


## Minium Requirements

All **users** should be able to (users can simultaneously be clients and agents):
- [X] Register a new account.
- [X] Login and Logout.
- [X] Edit their profile (at least name, username, password, and e-mail).


**Clients** should be able to:
- [X] Submit a new ticket optionally choosing a department (e.g., "Accounting").
- [X] List and track tickets they have submitted.
- [X] Reply to inquiries (e.g., the agent asks for more details) about their tickets and add more information to already submitted tickets.


**Agents** should be able to (they are also clients):
- [ ] List tickets from their departments (e.g., "Accounting"), and filter them in different ways (e.g., by date, by assigned agent, by status, by priority, by hashtag).
- [X] Change the department of a ticket (e.g., the client chose the wrong department).
- [X] Assign a ticket to themselves or someone else.
- [X] Change the status of a ticket. Tickets can have many statuses (e.g., open, assigned, closed); some may change automatically (e.g., ticket changes to "assigned" after being assigned to an agent).
- [ ] Edit ticket hashtags easily (just type hashtag to add (with autocomplete), and click to remove).
- [X] List all changes done to a ticket (e.g., status changes, assignments, edits).
- [X] Manage the FAQ and use an answer from the FAQ to answer a ticket.

**Admins** should be able to (they are also agents):
- [X] Upgrade a client to an agent or an admin.
- [X] Add new departments, statuses, and other relevant entities.
- [X] Assign agents to departments.
- [X] Control the whole system.


## Suggested additional requirements

- [ ] Tickets can have documents attached to them (both by clients and agents).
- [ ] Admins should be able to see key performance indicators and other statistics (e.g., number of tickets closed by agent, number of open tickets per day).
- [X] Agents can belong to more than one department.
- [ ] Agents can see a client's history.
- [X] Agents can watch tickets not assigned to them (e.g., when transferring a ticket, the agent can check a box stating that he still wants to follow the ticket).
- [ ] Tickets can be merged together or marked as duplicates from another ticket.
- [ ] Tickets can have to-do lists that must be completed before the ticket is closed.
- [X] Tasks can also be assigned to agents.

If you prefer, you can create your own additional requirements.

## Evaluation

Evaluation will be done on the following topics:

- Complexity (e.g., implemented features).
- Security (e.g., XSS, CSRF, injection, password storage).
- Technology (e.g., correct usage of HTML, CSS, Javascript, Ajax, No frameworks).
- Quality (e.g., code quality, file organization, consistency).
- User Interface (e.g., usability, consistency).
