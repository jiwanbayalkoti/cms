# BOQ Bill Statement: Running Bill & Final Bill – Features & Calculations

## अहिलेको Running Bill मा के छ (Current)

- **Source:** सबै Completed Work Records (project filter अनुसार) बाट live calculation
- **Aggregation:** Work → BOQ Item अनुसार: total completed qty, total amount (qty × rate), remaining qty (BOQ qty − completed)
- **Tax:** 13% fixed on subtotal → Subtotal + Tax = Total
- **Bill date:** Company को `bill_date` वा सबैभन्दा ठूलो record date
- **Export:** Excel (same structure)
- **Header:** Company, Client, Project, Contract No (company table बाट – project-wise होइन)

---

## Running Bill मा थप्न सकिने (Improvements)

| Feature | विवरण |
|--------|--------|
| **Bill period (date range)** | "This bill" को लागि from-date / to-date राखी त्यो period भित्रको completed work मात्र लिने; "Up to date" total पनि optional |
| **Previous + This bill columns** | Table मा: Previous bill (पहिलेको total), This bill (यो period को amount), Total to date |
| **Bill history / Save bill** | प्रत्येक running bill लाई save गर्ने (bill_no, date, period, amount, retention, net) ताकि अर्को पटक "previous bill" auto आउन सके |
| **Retention (e.g. 5% / 10%)** | Subtotal मा retention % लगाएर "Retention this bill" र "Total retention to date" देखाउने |
| **TDS / Deductions** | TDS % वा fixed deduction; Net payable = Total − Retention − TDS |
| **Project in header** | Dropdown बाट छानिएको project को name/number header मा देखाउने (company.project होइन) |
| **Tax % configurable** | 13% company वा setting बाट change गर्न सक्ने |

---

## Final Bill को लागि Features & Calculations

### 1. **Final Bill = Last Bill (समाप्ति बिल)**

- **Bill type:** Running vs **Final** select गर्ने; Final छान्दा:
  - "This is Final Bill" / "Contract completion" जस्तो label
  - अगाडि को सबै running bills को total + यो final amount = **Total contract value billed**
- **Calculation:**  
  - **Total work value (BOQ):** सबै BOQ items को (qty × rate) को जम्मा  
  - **Total completed (billed):** सबै completed work को जम्मा (अहिले जस्तै)  
  - Final bill मा बाँकी quantity (remaining qty × rate) पनि "this bill" मा लिई **100% completion** देखाउन सकिन्छ वा  
  - बाँकी राखी "work done up to final bill" मात्र पनि।

### 2. **Retention (रोकाइ) – Final मा release**

- **Running bills:** हरेक बिलमा retention % (e.g. 10%) लगाएर रोक्ने; "Retention this bill", "Total retention to date"
- **Final bill:**
  - **Total retention so far** (सबै running + final मा रोकिएको)
  - **Release of retention:** Final मा सबै वा केही % release गर्ने; "Released this bill", "Balance retention"
  - **Net payable (final):** Total − TDS − (Total retention − Released) वा जस्तो तपाईंको term।

### 3. **Deductions (कटौती)**

- **TDS:** % वा amount; Final मा "TDS to date" वा "TDS this bill" दुवै देखाउन सकिन्छ
- **Other deductions:** Penalty, claim, advance adjust, etc. – line items को रूपमा थप्न सकिन्छ
- **Formula:**  
  **Net payable = Subtotal + Tax − Retention − TDS − Other deductions + Retention released (if any)**

### 4. **Advance / Adjustments**

- **Advance given to client (contract advance):**  
  यदि client लाई advance दिइसकेको छ भने:  
  **Net payable = (Total − Retention − TDS) − Advance already paid**
- **Balance due:** Total billed − Total paid (previous payments) − Advance − Retention held

### 5. **Summary Block (Final Bill मा)**

- Contract value (BOQ total)
- Total billed (all bills / up to final)
- Previous payments / Advance
- Retention (held / released)
- TDS / Deductions
- **Balance due (final)** वा **Amount payable this bill**

### 6. **Bill History / Numbering**

- हरेक bill (running वा final) save गर्दा:
  - Bill no, Bill date, Period (from–to), Type (Running/Final)
  - Subtotal, Tax, Retention, TDS, Net amount
- Final bill बनाउँदा "Previous bills" सूची र तिनको total auto लिई "This bill" र "Total to date" निकाल्न सकिन्छ।

### 7. **Optional: Defect Liability Period (DLP)**

- DLP समाप्त भएपछि retention release गर्ने rule; Final bill मा "Retention released (after DLP)" जस्तो note।

---

## Implementation को लागि सुझाव (Short)

1. **Running bill:**  
   - Bill period (from/to date) filter  
   - Project dropdown र header मा selected project  
   - Optional: retention %, TDS %, र "Previous bill" को लागि **saved bills** table (bill_no, date, amount, retention, project_id, type)

2. **Final bill:**  
   - "Bill type: Final" option  
   - Same calculation + Retention release + Advance/TDS summary  
   - Contract total vs Total billed vs Balance due block  
   - Final bill पनि save गरेर bill history मा "Final" type ले राख्ने  

3. **Database (यदि bill save गर्ने हो):**  
   - `boq_bills` वा `running_bills`: id, company_id, project_id, bill_no, bill_date, period_from, period_to, type (running/final), subtotal, tax_percent, tax_amount, retention_percent, retention_amount, tds_amount, other_deductions, total, net_payable, notes  
   - Optional: `boq_bill_items` (per-bill item snapshot) यदि item-level history चाहिएको भए।

यो document अनुसार तपाईंले चाहेको level (simple final total मात्र वा retention/TDS/advance सहित full final bill) लाई step-by-step implement गर्न सकिन्छ।
