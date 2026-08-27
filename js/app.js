// --- EXPERIMENT DATA ---
// Yahan par sirf naya experiment add karna hai, card khud ban jayega!
const experiments = [
    { id: 1, title: "Experiment 1", desc: "List Departmental Details" },
    { id: 2, title: "Experiment 2", desc: "CSS Flexbox & Grid Layouts" },
    { id: 3, title: "Experiment 3", desc: "Fetch API & JSON Data" }
    // Jab exp4 karega, bas yahan likh dena: { id: 4, title: "Experiment 4", desc: "Topic name" },
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
function loadExperiment(expNum) {
    // Yahan dekh, ye dynamically experiments/exp1/index.html ko fetch karega
    appContent.innerHTML = `<iframe src="experiments/exp${expNum}/index.html" frameborder="0" id="exp-iframe"></iframe>`;
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