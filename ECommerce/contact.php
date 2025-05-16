<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In a real application, you would send an email here
        // For now, just store the message in the database
        
        $query = "INSERT INTO contact_messages (name, email, subject, message) 
                 VALUES ('$name', '$email', '$subject', '$message')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Thank you for your message. We will get back to you soon!';
            
            // Clear form fields after successful submission
            $name = $email = $subject = $message = '';
        } else {
            $error = 'There was a problem sending your message. Please try again.';
        }
    }
}
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Contact Us</h1>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col lg:flex-row">
            <!-- Contact Form -->
            <div class="w-full lg:w-2/3 lg:pr-8 mb-8 lg:mb-0">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Send Us a Message</h2>
                    
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Your Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" class="w-full border rounded-md px-4 py-2" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                            <input type="text" id="subject" name="subject" value="<?php echo isset($subject) ? $subject : ''; ?>" class="w-full border rounded-md px-4 py-2" required>
                        </div>
                        
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                            <textarea id="message" name="message" rows="6" class="w-full border rounded-md px-4 py-2" required><?php echo isset($message) ? $message : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-800 transition">Send Message</button>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Contact Information</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">                            <div class="flex-shrink-0 mt-1">                                <i class="fas fa-map-marker-alt text-gray-600"></i>                            </div>                            <div class="ml-3">                                <h3 class="font-medium">Address</h3>                                <p class="text-gray-600">A R Tanni Fashion<br>Shewrapara, Mirpur, Dhaka-1216<br>Bangladesh</p>                            </div>                        </div>                                                <div class="flex items-start">                            <div class="flex-shrink-0 mt-1">                                <i class="fas fa-phone-alt text-gray-600"></i>                            </div>                            <div class="ml-3">                                <h3 class="font-medium">Phone</h3>                                <p class="text-gray-600">+880 1700 000000</p>                            </div>                        </div>                                                <div class="flex items-start">                            <div class="flex-shrink-0 mt-1">                                <i class="fas fa-envelope text-gray-600"></i>                            </div>                            <div class="ml-3">                                <h3 class="font-medium">Email</h3>                                <p class="text-gray-600">info@artizo.com.bd</p>                            </div>                        </div>                                                <div class="flex items-start">                            <div class="flex-shrink-0 mt-1">                                <i class="fas fa-clock text-gray-600"></i>                            </div>                            <div class="ml-3">                                <h3 class="font-medium">Working Hours</h3>                                <p class="text-gray-600">                                    Saturday - Thursday: 10AM - 8PM<br>                                    Friday: 3PM - 8PM<br>                                </p>                            </div>                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Follow Us</h2>
                    
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-600 text-white rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-400 text-white rounded-full flex items-center justify-center hover:bg-blue-500 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700 transition">
                            <i class="fab fa-pinterest-p"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map -->
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Our Location</h2>
                
                                <div class="aspect-w-16 aspect-h-9">                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.2809767578177!2d90.37440927609358!3d23.77560528673843!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c0b3070a430f%3A0x19e8efa65f7db68d!2sA%20R%20Tanni%20Fashion!5e0!3m2!1sen!2sbd!4v1686567834576!5m2!1sen!2sbd"                         width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 