export function loadMessagesAdminView(){
    const container = document.getElementById("messageContainer");

    fetch("fetch_messages.php")
        .then(res => res.json())
        .then(data => {
            if(!Array.isArray(data)){
                container.innerHTML = "<p>No messages found.</p>";
                return;
            }

            const section = document.createElement("section");
            section.classList.add("message-container");

            data.forEach(msg => {
                const card = document.createElement("div");
                card.classList.add("message-card");
                card.dataset.id = msg.id;

                card.innerHTML = `
                    <div class="card-header">
                        <strong>${msg.email}</strong> <span>${msg.created_at}</span>
                    </div>
                    <div class="card-topic">${msg.topic}</div>
                    <div class="card-body">${msg.message}</div>
                    <div class="card-actions">
                        <button class="delete-btn">🗑️ Delete</button>
                    </div>
                `;

                section.appendChild(card);
            });

            container.innerHTML = "";
            container.appendChild(section);

            section.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const card = this.closest(".message-card");
                    const id = card.dataset.id;
            
                    if (confirm("Are you sure you want to delete this message?")) {
                        fetch("delete_message.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `id=${encodeURIComponent(id)}`
                        })
                        .then(res => res.text())
                        .then(result => {
                            console.log(result);
                            card.remove(); // Remove the card instead of table row
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