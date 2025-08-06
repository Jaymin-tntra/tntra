const SUPABASE_URL = "https://zmriofxctebhbprcmdsl.supabase.co";
const SUPABASE_API_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InptcmlvZnhjdGViaGJwcmNtZHNsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTQwNDcwMDAsImV4cCI6MjA2OTYyMzAwMH0.AmvRdbVnTShRA4-PfiGmwo_YmNInL0GcQsQ_oZVDHoA";
const TABLE_NAME = "inquiry";

async function fetchInquiries() {
  try {
    const response = await fetch(`${SUPABASE_URL}/rest/v1/${TABLE_NAME}?select=*`, {
      headers: {
        "apikey": SUPABASE_API_KEY,
        "Authorization": `Bearer ${SUPABASE_API_KEY}`,
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) throw new Error("Failed to fetch inquiries");

    const data = await response.json();
    renderTable(data);
  } catch (error) {
    console.error("Error fetching inquiries:", error.message);
  }
}

function renderTable(inquiries) {
  const tbody = document.getElementById("inquiry-body");
  tbody.innerHTML = "";

  inquiries.forEach(inquiry => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${inquiry.full_name || "-"}</td>
      <td>${inquiry.email || "-"}</td>
      <td>${inquiry.phone_no || "-"}</td>
      <td>${inquiry.user_type || "-"}</td>
      <td>${inquiry.purpose || "-"}</td>
      <td>${inquiry.property_type || "-"}</td>
      <td>${inquiry.preferred_location || "-"}</td>
      <td>${inquiry.budget_range || "-"}</td>
      <td>${inquiry.message || "-"}</td>
      <td>${new Date(inquiry.created_at).toLocaleString()}</td>
    `;
    tbody.appendChild(row);
  });
}

// Initialize on page load
fetchInquiries();
