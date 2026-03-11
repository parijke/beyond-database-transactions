# Slot 4: Distributed Business Processes (Sagas)

In this slot, we look at how complex processes across multiple services can be coordinated without having to rely on classic distributed transactions (2PC).

## Orchestration vs. Choreography

When a business process involves multiple services, the question arises: Who controls the process?

### Choreography (Event-Driven)
In choreography, there is no central coordinator. Each participating service knows which events it must react to and which events it must trigger itself after completing its work.

*   **Metaphor:** Dancers on a stage who know their steps and react to each other without a conductor announcing every step.
*   **Pros:**
    *   Low coupling: Services do not need to know each other, only the events.
    *   No single point of failure in process management.
    *   Easy extensibility (new services simply "listen in").
*   **Cons:**
    *   The overall process is hard to oversee ("Where is the order right now?").
    *   Debugging and monitoring are more complex.
    *   Risk of cyclic dependencies.

### Orchestration (Command-Driven)
In orchestration, there is a central "orchestrator" (manager) that manages the state of the process and gives explicit commands to the participating services.

**Important distinction: Synchronous vs. Asynchronous**
*   **Synchronous Orchestration:** The orchestrator calls other services directly (e.g., via HTTP/REST or RPC) and waits for the result. This is easier to implement but blocks resources and is more prone to timeouts.
*   **Asynchronous Orchestration:** The orchestrator sends commands via a message broker and reacts to response events. This is highly scalable and resilient but requires a persistent state machine in the orchestrator.

In this training, we use **synchronous orchestration** to focus on the logic of compensation without introducing the complexity of state machines and asynchronous message waiting. The "process boundaries" are simulated by separate databases.

A conceptual example for an **asynchronous Saga** can be found in `02_orchestration/async_saga_concept.php`.

*   **Metaphor:** A conductor who tells every musician in the orchestra exactly when to play what.
*   **Pros:**
    *   Central control and overview of process progress.
    *   Easier error handling and coordination of compensations.
    *   Good visibility of business logic in one place.
*   **Cons:**
    *   Tighter coupling: The orchestrator must know the interfaces of the services.
    *   The orchestrator can become a bottleneck or single point of failure.
    *   Logic tends to "migrate" into the orchestrator, while services degenerate into pure data holders.

---

## Sagas & Compensation

Since we have no real rollback across service boundaries in distributed systems, we use the **Saga Pattern**. A saga is a sequence of local transactions.

If a step in the saga fails, the steps already successfully completed must be undone by **compensating transactions** (e.g., "refund money" instead of "rollback the charge").

### Tasks in this slot:
1.  **Understand choreography:** Analyze how events flow between services.
2.  **Implement orchestration:** Build an `OrderManager` that controls the process.
3.  **Implement compensation:** What happens if the payment fails but the warehouse has already reserved?
