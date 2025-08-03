export function loadMessagesAdminView(){
    const container = document.getElementById("messageContainer");

    fetch("fetch_messages.php")
        .then(res => res.json())
        .then(data => {
            if(!Array.isArray(data)){
                container.innerHTML = "<p>No messages found.</p>";
                return;
            }

            const table = document.createElement("table");
            table.classList.add("msg-table");
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Topic</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(msg => `
                        <tr data-id="${msg.id}">
                            <td>${msg.email}</td>
                            <td>${msg.topic}</td>
                            <td>${msg.message}</td>
                            <td>${msg.created_at}</td>
                            <td><button class="delete-btn">🗑️ Delete</button></td>
                        </tr>
                        `).join("")}
                </tbody>
            `;
            container.innerHTML = "";
            container.appendChild(table);

            table.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const row = this.closest("tr");
                    const id = row.dataset.id;
                    if (confirm("Are you sure you want to delete this message?")) {
                        fetch("delete_message.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `id=${id}`
                        })
                        .then(res => res.text())
                        .then(result => {
                            console.log(result);
                            row.remove();
                        })
                        .catch(err => console.error("Delete failed", err));
                    }
                });
            });
        })
        .catch(err => {
            container.innerHTML = "<p>Error loading messages</p>";
            console.error(err);
        });

}