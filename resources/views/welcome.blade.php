<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Lab - Master Your Musical Journey</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero {
            background: linear-gradient(
                rgba(0, 0, 0, 0.65),    /* stronger dark overlay top */
                rgba(0, 0, 0, 0.75)     /* even darker at bottom for better readability */
            ), 
            url('{{ asset("build/assets/images/hero/hero.jpg") }}');  /* ← change to your actual filename, e.g. hero-main.jpg */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-content {
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(6px);
        }

        .hero h1 {
            text-shadow: 3px 3px 12px rgba(0,0,0,0.8);
            letter-spacing: -1px;
        }

        .hero p {
            text-shadow: 1px 1px 6px rgba(0,0,0,0.7);
        }

        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .star:hover,
        .star.active {
            color: #d4af7a;
        }
    </style>
</head>
<body class="text-gray-800 leading-relaxed">

    <header class="bg-white/95 px-[5%] py-3 fixed w-full top-0 z-[1000] shadow-md flex justify-between items-center">
        <div class="flex items-center gap-2 text-xl font-bold text-gray-800">
            <svg class="w-8 h-8 text-[#d4af7a]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
            </svg>
            Music Lab
        </div>
        <div class="flex gap-3">
            <a href="/login" class="bg-[#5a6c7d] text-white px-6 py-2 rounded-md text-sm transition-colors hover:bg-[#4a5c6d]">Log In</a>
           <a href="/register" class="bg-[#d4af7a] text-gray-800 px-6 py-2 rounded-md text-sm font-medium transition-colors hover:bg-[#c49d68]">Register</a>
        </div>
    </header>

    <section class="hero min-h-[500px] h-[70vh] flex flex-col justify-center items-center text-center text-white px-6 relative mt-14">
        <div class="hero-content p-8 rounded-2xl max-w-[800px] shadow-[0_10px_40px_rgba(0,0,0,0.4)]">
            <h1 class="text-4xl md:text-5xl font-black mb-4">Master Your Musical Journey</h1>
            <p class="text-base md:text-lg max-w-[700px] mb-8 leading-relaxed opacity-95">Learn from expert instructors, access premium instruments, and unlock your musical potential with our comprehensive online music education platform</p>
            <div class="flex gap-4 flex-wrap justify-center">
                <button class="px-8 py-3 text-sm font-semibold rounded-full cursor-pointer transition-all shadow-[0_6px_20px_rgba(0,0,0,0.3)] bg-[#d4af7a] text-gray-800 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(0,0,0,0.4)]" onclick="document.getElementById('lessons').scrollIntoView({behavior: 'smooth'})">Start Learning Today</button>
                <button class="px-8 py-3 text-sm font-semibold rounded-full cursor-pointer transition-all shadow-[0_6px_20px_rgba(0,0,0,0.3)] bg-white/15 text-white border-2 border-white/40 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(0,0,0,0.4)]" onclick="document.getElementById('lessons').scrollIntoView({behavior: 'smooth'})">Explore Courses</button>
            </div>
        </div>
    </section>

    <section class="py-16 px-[5%] bg-gray-100 text-center">
        <h2 class="text-3xl font-bold mb-3 text-gray-800">Welcome to Music Lab</h2>
        <p class="max-w-[800px] mx-auto mb-10 text-gray-600 text-base">Your premier destination for online music education and quality instruments. We connect passionate learners with world-class instructors and provide access to professional-grade musical instruments.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10 max-w-3xl mx-auto">
            <div class="bg-white p-6 rounded-xl shadow-md transition-transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-[#d4af7a] rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-gray-800">Online Lessons</h3>
                <p class="text-gray-600 text-sm">Access high-quality video lessons anytime, anywhere with our flexible learning platform</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md transition-transform hover:-translate-y-1">
                <div class="w-12 h-12 bg-[#d4af7a] rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-gray-800">Quality Instruments</h3>
                <p class="text-gray-600 text-sm">Shop from our curated selection of professional instruments and accessories</p>
            </div>
        </div>
    </section>

    <section id="lessons" class="py-16 px-[5%] bg-white">
        <h2 class="text-center text-3xl font-bold mb-3 text-gray-800">Instrument Lessons</h2>
        <p class="text-center text-gray-600 mb-10 text-base">Choose from our comprehensive range of instrument courses designed for all skill levels</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Aluminum Snare Drum.jpg') }}" alt="Aluminum Snare Drum" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Snare Drum</h3>
                    <p class="text-gray-600 mb-3 text-sm">Master rudiments, grooves, and dynamic control on high-quality aluminum snare drums</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 24 Lessons</span>
                        <span>Beginner to Advanced</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/American Red Gum.jpg') }}" alt="American Red Gum" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Drum Kit</h3>
                    <p class="text-gray-600 mb-3 text-sm">Learn fingerstyle, strumming, and chords on beautiful American red gum acoustics</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 32 Lessons</span>
                        <span>All Levels</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Beatbox.jpg') }}" alt="Beatbox" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Beatbox & Cajon</h3>
                    <p class="text-gray-600 mb-3 text-sm">Develop rhythm, bass tones, and percussion skills using cajon and beatbox techniques</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 20 Lessons</span>
                        <span>Beginner to Intermediate</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Chameleon.jpg') }}" alt="Chameleon" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Acoustic Guitar</h3>
                    <p class="text-gray-600 mb-3 text-sm">Explore leads, rhythms, and effects on versatile Chameleon finish electrics</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 30 Lessons</span>
                        <span>Intermediate to Advanced</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Dark Teal.jpg') }}" alt="Dark Teal" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Drum Kit</h3>
                    <p class="text-gray-600 mb-3 text-sm">Build power, timing, and fills on striking Dark Teal drum kits</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 26 Lessons</span>
                        <span>All Levels</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Division Black Maple.jpg') }}" alt="Division Black Maple" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Rock Drums</h3>
                    <p class="text-gray-600 mb-3 text-sm">Focus on heavy grooves and dynamics with Division Black Maple shells</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 22 Lessons</span>
                        <span>Intermediate</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Drums.jpg') }}" alt="Drums" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Complete Drums</h3>
                    <p class="text-gray-600 mb-3 text-sm">Full drum set training – from basics to advanced independence</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 40 Lessons</span>
                        <span>All Levels</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Piano.jpg') }}" alt="Piano" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Piano & Keyboard</h3>
                    <p class="text-gray-600 mb-3 text-sm">Learn scales, chords, and songs on digital and acoustic pianos</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 35 Lessons</span>
                        <span>Beginner to Advanced</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Raven.jpg') }}" alt="Raven" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Modern Guitar</h3>
                    <p class="text-gray-600 mb-3 text-sm">Contemporary styles and techniques on Raven series electrics</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 28 Lessons</span>
                        <span>Intermediate</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Resurrector.jpg') }}" alt="Resurrector" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Bass Guitar</h3>
                    <p class="text-gray-600 mb-3 text-sm">Groove, slap, and fingerstyle on powerful Resurrector basses</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 25 Lessons</span>
                        <span>Beginner to Advanced</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                <img src="{{ asset('build/assets/images/instruments/Sub Zero White Maple.jpg') }}" alt="Sub Zero White Maple" class="w-full h-44 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Jazz & Fusion Drums</h3>
                    <p class="text-gray-600 mb-3 text-sm">Brushwork, swing, and complex rhythms on Sub Zero White Maple kits</p>
                    <div class="flex justify-between items-center mb-3 text-xs text-gray-500">
                        <span>📚 30 Lessons</span>
                        <span>Advanced</span>
                    </div>
                    <button class="w-full py-2 bg-gray-800 text-white text-sm rounded-md cursor-pointer transition-colors hover:bg-gray-900">View Course</button>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-[5%] bg-gray-100">
        <h2 class="text-center text-3xl font-bold mb-3 text-gray-800">Our Products</h2>
        <p class="text-center text-gray-600 mb-2 text-base">Premium instruments and accessories for musicians at every level</p>
        <p class="text-center text-gray-600 mb-8 text-sm">Visit our store to purchase these quality instruments</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 max-w-6xl mx-auto">

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Aluminum Snare Drum.jpg') }}" alt="Aluminum Snare Drum" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Snare</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Aluminum Snare Drum</h3>
                    <div class="text-xl font-bold text-gray-800">₱9,999</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/American Red Gum.jpg') }}" alt="American Red Gum" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Drum Kit</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">American Red Gum</h3>
                    <div class="text-xl font-bold text-gray-800">₱10,500</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Beatbox.jpg') }}" alt="Beatbox" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Cajon</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Beatbox Cajon</h3>
                    <div class="text-xl font-bold text-gray-800">₱6,800</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Chameleon.jpg') }}" alt="Chameleon" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Acoustic Guitar</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Chameleon</h3>
                    <div class="text-xl font-bold text-gray-800">₱3,900</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Dark Teal.jpg') }}" alt="Dark Teal" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Drum Kit</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Dark Teal Maple</h3>
                    <div class="text-xl font-bold text-gray-800">₱6,000</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Division Black Maple.jpg') }}" alt="Division Black Maple" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Drum Kit</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Division Black Maple</h3>
                    <div class="text-xl font-bold text-gray-800">₱7,500</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Drums.jpg') }}" alt="Drums" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Full Kit</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Professional Drum Set</h3>
                    <div class="text-xl font-bold text-gray-800">₱95,000</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Piano.jpg') }}" alt="Piano" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Digital Piano</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Digital Piano 88-Key</h3>
                    <div class="text-xl font-bold text-gray-800">₱8,900</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Raven.jpg') }}" alt="Raven" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Electric Guitar</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Raven Series</h3>
                    <div class="text-xl font-bold text-gray-800">₱10,500</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Resurrector.jpg') }}" alt="Resurrector" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Electric Guitar</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Resurrector</h3>
                    <div class="text-xl font-bold text-gray-800">₱10,900</div>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('build/assets/images/instruments/Sub Zero White Maple.jpg') }}" alt="Sub Zero White Maple" class="w-full h-48 object-cover border-b border-gray-200">
                <div class="p-4">
                    <span class="inline-block bg-[#d4af7a] text-gray-800 px-3 py-1 rounded-full text-xs mb-2">Drum Kit</span>
                    <h3 class="text-base font-semibold mb-1 text-gray-800">Sub Zero White Maple</h3>
                    <div class="text-xl font-bold text-gray-800">₱9,900</div>
                </div>
            </div>
        </div>
        
        <button class="block mx-auto mt-10 px-8 py-3 bg-gray-800 text-white text-sm rounded-md cursor-pointer hover:bg-gray-900" onclick="alert('Visit our store at Mango Square Mall, Juana Osmeña, Brgy. Kamputhaw 6000 Cebu City, Philippines to see all products!')">View All Products</button>
    </section>

    <section class="py-16 px-[5%] bg-white">
        <h2 class="text-center text-3xl font-bold mb-3 text-gray-800">Leave Your Review</h2>
        <p class="text-center text-gray-600 mb-8 text-base">Share your experience with Music Lab</p>
        
        <div class="max-w-xl mx-auto bg-gray-100 p-6 rounded-xl">
            <form id="reviewForm">
                <div class="mb-5">
                    <label class="block mb-2 text-gray-800 font-medium text-sm">Your Rating</label>
                    <div class="flex gap-2 text-3xl" id="starRating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" id="ratingValue" value="0">
                </div>
                
                <div class="mb-5">
                    <label for="reviewerName" class="block mb-2 text-gray-800 font-medium text-sm">Your Name</label>
                    <input type="text" id="reviewerName" required placeholder="Enter your name" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                
                <div class="mb-5">
                    <label for="reviewText" class="block mb-2 text-gray-800 font-medium text-sm">Your Review</label>
                    <textarea id="reviewText" required placeholder="Share your experience..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm resize-y min-h-[100px]"></textarea>
                </div>
                
                <button type="submit" class="w-full py-3 bg-[#d4af7a] text-gray-800 text-sm rounded-md cursor-pointer font-medium hover:bg-[#c49d68]">Submit Review</button>
            </form>
        </div>

        <div class="max-w-3xl mx-auto mt-10" id="reviewsList">
            <h3 class="text-center mb-6 text-xl font-semibold text-gray-800">Recent Reviews</h3>
            <div class="bg-gray-100 p-5 rounded-xl mb-4">
                <div class="flex justify-between mb-2">
                    <span class="font-bold text-gray-800 text-sm">Sarah Johnson</span>
                    <span class="text-[#d4af7a] text-sm">★★★★★</span>
                </div>
                <p class="text-gray-700 text-sm">Excellent platform! The piano lessons are well-structured and the instructors are very professional. Highly recommend!</p>
            </div>
            
            <div class="bg-gray-100 p-5 rounded-xl mb-4">
                <div class="flex justify-between mb-2">
                    <span class="font-bold text-gray-800 text-sm">Mike Chen</span>
                    <span class="text-[#d4af7a] text-sm">★★★★★</span>
                </div>
                <p class="text-gray-700 text-sm">Great selection of instruments and the quality is top-notch. The guitar lessons helped me improve so much!</p>
            </div>
        </div>
    </section>

    <section class="py-16 px-[5%] bg-gray-800 text-center text-white">
        <h2 class="text-3xl font-bold mb-3">Ready to Start Your Musical Journey?</h2>
        <p class="text-base mb-8 max-w-2xl mx-auto">Join thousands of students learning music online with Music Lab. Get access to expert instruction and quality instruments today.</p>
        <button class="px-10 py-3 bg-[#d4af7a] text-gray-800 text-sm rounded-md cursor-pointer font-medium hover:bg-[#c49d68]" onclick="alert('Registration functionality to be implemented')">Get Started Now</button>
    </section>

    <footer class="bg-[#3d4f5d] text-white py-12 px-[5%]">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div>
                <h3 class="mb-3 text-lg font-semibold">Music Lab</h3>
                <p class="text-sm text-gray-300">Your premier destination for online music education and quality instruments.</p>
            </div>
            
            <div>
                <h3 class="mb-3 text-lg font-semibold">Courses</h3>
                <ul class="list-none space-y-2">
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Piano Lessons</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Guitar Lessons</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Violin Lessons</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Drum Lessons</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="mb-3 text-lg font-semibold">Shop</h3>
                <ul class="list-none space-y-2">
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Instruments</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Accessories</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Sheet Music</a></li>
                    <li><a href="#" class="text-sm text-gray-300 hover:text-[#d4af7a] transition-colors">Gift Cards</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="mb-3 text-lg font-semibold">Contact</h3>
                <ul class="list-none space-y-2 text-sm text-gray-300">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <span>musiclabcebu@gmail.com</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        <span>0933 173 0733</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Mango Square Mall, Juana Osmeña, Brgy. Kamputhaw 6000 Cebu City, Philippines</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="text-center pt-8 border-t border-gray-600 text-sm text-gray-400">
            <p>© 2026 Music Lab. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const stars = document.querySelectorAll('.star');
        const ratingValue = document.getElementById('ratingValue');
        let selectedRating = 0;

        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = this.getAttribute('data-rating');
                ratingValue.value = selectedRating;
                updateStars(selectedRating);
            });

            star.addEventListener('mouseover', function() {
                const hoverRating = this.getAttribute('data-rating');
                updateStars(hoverRating);
            });
        });

        document.getElementById('starRating').addEventListener('mouseout', function() {
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            stars.forEach(star => {
                const starRating = star.getAttribute('data-rating');
                if (starRating <= rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = ratingValue.value;
            const name = document.getElementById('reviewerName').value;
            const text = document.getElementById('reviewText').value;

            if (rating == 0) {
                alert('Please select a rating');
                return;
            }

            const reviewsList = document.getElementById('reviewsList');
            const newReview = document.createElement('div');
            newReview.className = 'bg-gray-100 p-5 rounded-xl mb-4';
            
            const starDisplay = '★'.repeat(rating) + '☆'.repeat(5 - rating);
            
            newReview.innerHTML = `
                <div class="flex justify-between mb-2">
                    <span class="font-bold text-gray-800 text-sm">${name}</span>
                    <span class="text-[#d4af7a] text-sm">${starDisplay}</span>
                </div>
                <p class="text-gray-700 text-sm">${text}</p>
            `;
            
            reviewsList.insertBefore(newReview, reviewsList.children[1]);
            
            this.reset();
            selectedRating = 0;
            updateStars(0);
            
            alert('Thank you for your review!');
        });
    </script>

</body>
</html>