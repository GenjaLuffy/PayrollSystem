//profile edit

const profileImage = document.getElementById('profileImage');
        const fileInput = document.getElementById('profileImageInput');

        profileImage.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (event) => {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        });