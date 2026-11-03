// --- EXPERIMENT DATA ---
// Yahan par sirf naya experiment add karna hai, card khud ban jayega!
const experiments = [
    { id: 1, title: "Experiment 1", desc: "List Departmental Details", file: "index.html" },
    { id: 2, title: "Experiment 2", desc: "CSS Flexbox & Grid Layouts", file: "index.html" },
    { id: 3, title: "Experiment 3", desc: "JavaScript and Interactive Web Pages", file: "index.html" },
    { id: 4, title: "Experiment 4", desc: "XML & XSL Course Catalogue", file: "courses.xml" },
    { id: 5, title: "Experiment 5", desc: "Dynamic PHP Student Result Management", file: "index.php" },
    { id: 6, title: "Experiment 6", desc: "PHP & MySQL CRUD Operations", file: "index.php" },
    { id: 7, title: "Experiment 7", desc: "Authentication with Sessions & Cookies", file: "index.php" }
];

const appContent = document.getElementById('app-content');
const homeBtn = document.getElementById('home-btn');

// --- FUNCTION TO BUILD CARDS ---
function renderGrid() {
    let cardsHTML = '<div class="grid-container">';
    
    experiments.forEach(exp => {
        cardsHTML += `
            <div class="card" onclick="loadExperiment(${exp.id})">
                <h3>${exp.title}</h3>
                <p>${exp.desc}</p>
                <span class="status">Click to View</span>
            </div>
        `;
    });
    
    cardsHTML += '</div>';
    appContent.innerHTML = cardsHTML;
}

// --- FUNCTION TO LOAD EXPERIMENT IN IFRAME ---
// Each experiment can use index.html, courses.xml, index.php, or any other entry file.
// Add the entry file in the experiment object's "file" property.
function loadExperiment(expNum) {
    const experiment = experiments.find(exp => exp.id === expNum);
    if (!experiment) return;

    const file = experiment.file || "index.html";
    const isPhp = file.endsWith(".php");

    if (isPhp) {
        appContent.innerHTML = `
            <section class="experiment-message">
                <h2>${experiment.title}</h2>
                <p>PHP cannot be executed by GitHub Pages.</p>
                <p>Run this experiment locally from the repository root:</p>
                <pre><code>php -S localhost:8000</code></pre>
                <p>Then open <a href="http://localhost:8000/experiments/exp${expNum}/${file}" target="_blank">http://localhost:8000/experiments/exp${expNum}/${file}</a>.</p>
                <a class="message-link" href="https://github.com/manikng/Adv-Web-Tech-LAB-DTU/tree/main/experiments/exp${expNum}" target="_blank" rel="noopener noreferrer">View PHP source on GitHub</a>
            </section>`;
    } else {
        appContent.innerHTML = `<iframe src="experiments/exp${expNum}/${file}" frameborder="0" id="exp-iframe"></iframe>`;
    }

    homeBtn.classList.remove('active');
}

// --- FUNCTION TO GO BACK TO HOME ---
homeBtn.addEventListener('click', () => {
    renderGrid(); // Grid wapas bana do
    homeBtn.classList.add('active');
});

// --- INITIAL LOAD ---
// Page jab open ho toh pehle grid bana ke dikhao
renderGrid();