alert("🔥 merged JS is working");
console.log("✅ merged working_files.js is executing");

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("schedule-modal");
    const arrivalBtn = document.getElementById("confirm-arrival");
    const departureBtn = document.getElementById("confirm-departure");

    const openModal = () => { modal.style.display = "block"; };
    window.closeSchedulePopup = () => {
        modal.style.display = "none";
        document.getElementById("arrival-section").style.display = "block";
        document.getElementById("departure-section").style.display = "none";
    };

    const showSchedule = document.querySelector(".cta-button");
    if (showSchedule) {
        showSchedule.addEventListener("click", (e) => {
            e.preventDefault();
            openModal();
        });
    }

    // ✅ Arrival logic (save only)
    if (arrivalBtn) {
        arrivalBtn.addEventListener("click", () => {
            const route = document.getElementById("arrival-route").value;
            const time = document.getElementById("arrival-time").value;

            if (route && time) {
                fetch("save_schedule.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ route, time_slot: time, type: "arrival" }),
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === "success") {
                        document.getElementById("arrival-section").style.display = "none";
                        document.getElementById("departure-section").style.display = "block";
                    }
                });
            } else {
                alert("Please fill all arrival fields.");
            }
        });
    }

    // ✅ Departure logic with redirect to rebook.php
    if (departureBtn) {
        departureBtn.addEventListener("click", () => {
            const route = document.getElementById("departure-route").value;
            const time = document.getElementById("departure-time").value;

            if (route && time) {
                fetch("save_schedule.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ route, time_slot: time, type: "departure" }),
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    console.log("✅ Redirecting to rebook.php...");
                    window.location.href = "rebook.php";
                });
            } else {
                alert("Please fill all departure fields.");
            }
        });
    }

    // 🖼️ Image hover logic
    const images = document.querySelectorAll(".image-stack .image");
    let currentIndex = 0;

    images.forEach((image) => {
        image.addEventListener("mouseover", () => {
            setTimeout(() => {
                images[currentIndex].classList.remove("active");
                currentIndex = (currentIndex + 1) % images.length;
                images[currentIndex].classList.add("active");
            }, 1000);
        });
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) closeSchedulePopup();
    });
});
