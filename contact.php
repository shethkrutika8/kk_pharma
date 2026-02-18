<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us | KK Pharmaceutical</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI";
        }

        body {
            background: #f4f6f8;
            color: #2c3e50;
        }

        /* grid vada content ne proper vache lavla mate*/
        .section {
            max-width: 1400px;
            margin: 50px auto;
            padding: 20px;
        }

        /*2  partition karva mate*/
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        /* card no color ne set karva mate and padding rakhva mate 
        ena headeing ne highlight karva mate ne paragraph na content ne set karva mate   */
        .card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            margin-bottom: 15px;
        }

        .card p {
            line-height: 1.7;
            margin-bottom: 15px;
            color: #34495e;
        }

        /*contact foem ma as list na formate ma na batave evu bana va mate list-style -none */
        .list li {
            margin-bottom: 10px;
            list-style: none;
        }

        /* form na input ne set karva mate ne box ne set karva mate  */
        form input,
        form textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

    /* text area ni size proer ne hiight define karva mate*/
        textarea {
            resize: none;
            height: 130px;
        }
        /*button no color change karva mate and eni fonet size ne curocr jai tyare pointer ne chnage karva mate*/
        button {
            background: #2c3e50;
            color: white;
            padding: 15px;
            font-size: 20px;
            border-radius: 10px;
            cursor: pointer;
        }

        button:hover {
            background: #1f2d3a;
        }



        /* wothout this grid nu formtte one line ma aave ene set karva mate*/ 
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }



        /* RESPONSIVE */
        @media(max-width:900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php include_once("header.php"); ?>

    <!--main section ne grid karva mate-->
    <div class="section">

        <div class="grid">

            <!--ek baju company no add karva mate info-->
            <div class="card">
                <h2>About KK Pharmaceutical</h2>
                <p>
                    KK Pharmaceutical is a research-driven pharmaceutical company
                    focused on developing, manufacturing, and distributing
                    high-quality medicines across therapeutic segments.
                </p>
                <p>
                    Our operations comply with WHO-GMP standards, ensuring safety,
                    efficacy, and consistency in every product we deliver.
                </p>

                <h3>📍 Corporate Office</h3>
                <p>
                    Pharma Industrial Estate, India
                </p>

                <h3> &#128340; Business Hours</h3>
                <ul class="list">
                    <li>Monday – Friday: 9:00 AM – 6:00 PM</li>
                    <li>Saturday: 9:00 AM – 2:00 PM</li>
                    <li>Sunday: Closed</li>
                </ul>
            </div>

            <!--ek baju contact form add kerva mate-->
            <div class="card">
                <h2>Contact Form</h2>

                <form onsubmit="return sendMessage()">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text"
                            name="firstname"
                            id="firstname"
                            data-validation="required min"
                            data-min="2">
                        <span class="text-danger" id="firstname_error"></span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email"
                            name="email"
                            id="email"
                            data-validation="required email">
                        <span id="email_error"></span>
                    </div>
                    <input type="text" id="subject" placeholder="Subject">
                    <textarea id="message" placeholder="Write your message"></textarea>
                    <button type="submit">Submit Inquiry</button>
                    <div id="status"></div>
                </form>
            </div>
        </div>

        <!-- DEPARTMENTS -->
        <div class="info-grid">
            <div class="card">
                <h3>&#128222; Sales & Marketing</h3>
                <p>sales@kkpharma.com<br>+91 98765 11111</p>
            </div>

            <div class="card">
                <h3>🧪 Medical & QA</h3>
                <p>medical@kkpharma.com<br>+91 98765 22222</p>
            </div>

            <div class="card">
                <h3>&#128230; Distribution</h3>
                <p>distribution@kkpharma.com<br>+91 98765 33333</p>
            </div>

            <div class="card">
                <h3>&#9888; Pharmacovigilance</h3>
                <p>
                    Report adverse drug reactions<br>
                    safety@kkpharma.com
                </p>
            </div>
        </div>

        <!-- FAQ -->
        <div class="card" style="margin-top:40px;">
            <h2>Frequently Asked Questions</h2>
            <p><strong>Do you offer third-party manufacturing?</strong><br>Yes, we provide WHO-GMP certified manufacturing services.</p>
            <p><strong>How can I become a distributor?</strong><br>Contact our sales department with your company profile.</p>
            <p><strong>Are your products regulatory approved?</strong><br>Yes, all products comply with national regulatory standards.</p>
        </div>



    </div>

    <?php include_once("footer.php"); ?>


</body>

</html>