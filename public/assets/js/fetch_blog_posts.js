window.addEventListener('load', () => {
    // fetch('http://localhost:8080/larrymayers.site/public/assets/posts/fetch_blog_posts.php')
    fetch('./assets/posts/fetch_blog_posts.php')
        .then(response => response.json())
        .then(data => {
            const contentScreen = document.getElementById('content_screen');
            contentScreen.innerHTML = '';

            if (data.error) {
                contentScreen.innerHTML = `<p>${data.error}</p>`;
            } else {
                data.forEach(post => {
                    const postDiv = document.createElement('div');
                    postDiv.classList.add('blog_post');

                    postDiv.innerHTML = `
                        <h2>${post.title}</h2>
                        <span><strong>Author:</strong> ${post.author}</span> | <span><strong>Date:</strong> ${post.date}</span>
                        <br><br>
                        <p class="post-content">${post.contents.replace(/\n/g, '<br>')}</p>
                    `;

                    contentScreen.appendChild(postDiv);
                });
            }

        })
        .catch(error => {
            document.getElementById('content_screen').innerHTML = `<p>Error loading blog posts: ${error}</p>`;
        });
});


function addPost(){
    const form = document.getElementById('blogForm');

    if(!form){
        console.error("Blog post web form not found");
        return; 
    }

    const blogTitle = form.querySelector('input[name="title"]').value;
    const blogAuthor = form.querySelector('input[name="author"]').value;
    const blogContents = form.querySelector('input[name="contents"]').value;

     // Create a JSON object with the form data
     const data = {
        title: blogTitle,
        author: blogAuthor,
        contents: blogContents
    };

    // fetch('http://localhost:8080/larrymayers.site/public/blog_post_insert.php', {
    fetch('./blog_post_insert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' 
        },
        body: JSON.stringify(data) /* Send the values collected via the FORM to the PHP file as a JSON Object */

    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            form.querySelector('input[name="title"]').value = null;
            form.querySelector('input[name="author"]').value = null;
            form.querySelector('input[name="contents"]').value = null;

            messageDiv.textContent = 'Blog entry submitted successfully!';
            // window.location.href = './blog.html'; // Redirect to blog.html
        } else {
            messageDiv.textContent = `Error: ${data.error}`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

