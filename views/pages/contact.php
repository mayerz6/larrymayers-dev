<?php ?>



        <main>
            <section id="contact">
                <div class="container">
                    <h1>Contact Me</h1>
                    
                     <form id="contactForm">
                        <fieldset>
                            <legend>Get in Touch</legend>           
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required>
                            <br>
                            <br>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                            <br>
                            <br>
                            <label for="message">Message:</label>
                            <textarea id="message" name="message" rows="5"></textarea>
                            <br>
                            <br>
                            <button type="submit">Send Message</button>
                        </fieldset>
                    </form>
                    <div id="formResMsg" class="text-small mt-2"></div>
                </div>
            </section>
        </main>

 