# Material: Size र Dual Unit (Bundle/Kg) – Design

## १. समस्या
- Rod जस्ता material को लागि **size** (८mm, १०mm, १२mm) र **quantity** दुवै unit मा चाहिन्छ: **Bundle** र **Kg**.
- Material name company अनुसार अलग हुन सक्छ (naming different), ID पनि company-wise different हुन सक्छ.

## २. हालको structure
- **Material names**: `material_names` table – company_id छ (company-wise list). तपाईंले dynamic name राख्नुहुन्छ।
- **Construction materials**: `construction_materials` – material_name, unit (एक मात्र), quantity_received, quantity_used, quantity_remaining.

## ३. सुझाव (Implementation)

### ३.१ Database (construction_materials मा थप field)
| Field | Type | Use |
|-------|------|-----|
| `size` | string, nullable | Rod को size: 8mm, 10mm, 12mm (वा जस्तो पनि लेख्न सक्नुहुन्छ) |
| `quantity_secondary` | decimal, nullable | दोस्रो unit मा quantity (जस्तै Kg) |
| `unit_secondary` | string, nullable | दोस्रो unit को नाम (जस्तै Kg, Piece) |

- **Primary**: quantity_received / quantity_used / quantity_remaining + **unit** (जस्तै Bundle).
- **Secondary**: quantity_secondary + **unit_secondary** (जस्तै Kg).
- Size र secondary field हरू optional – जहाँ चाहिन्छ (Rod) मात्र भर्ने।

### ३.२ Material name – Company wise
- `material_names` मा पहिले नै `company_id` छ। त्यसैले company अनुसार material name list अलग हुन्छ।
- नामिंग अलग राख्न: प्रत्येक company आफ्नो Material Names मा "Rod", "Steel Rod", "Rebar" जस्ता नाम add गर्छ – report/list मा material_name नै देखिन्छ (ID company-wise different हुन सक्छ, naming पनि different)।

### ३.३ Size – Flexible रख्न
- **Option A (सिधा)**: `size` एउटा text field – user ले 8mm, 10mm, 12mm टाइप गर्छ। कुनै पनि company आफ्नो convention use गर्न सक्छ।
- **Option B (भविष्यमा)**: Company ले size list define गर्न खोज्यो भने एउटा table `material_sizes` (company_id, material_name, size) बनाएर dropdown मा use गर्न सकिन्छ। पहिले Option A ले पनि काम गर्छ।

### ३.४ Form (Add/Edit Material)
- **Size** (optional): text वा dropdown – Rod को लागि 8mm, 10mm, 12mm.
- **Quantity (primary)**: जस्तै अहिले – e.g. 5, Unit: **Bundle**.
- **Quantity (secondary)** (optional): e.g. 120, Unit (secondary): **Kg**.

Report/List मा: Material name + size (अगर छ भने), Received/Used/Remaining (primary unit), र secondary quantity (अगर छ भने) देखाउन सकिन्छ।

### ३.५ Summary
- Rod को लागि: material_name = Rod (वा company को नाम), size = 10mm, quantity_received = 5, unit = Bundle, quantity_secondary = 120, unit_secondary = Kg.
- Company नुसार material name अलग राख्न: material_names company_id ले नै अलग हुन्छ; construction_materials पनि company_id ले scope छ।
- Implementation: migration ले size, quantity_secondary, unit_secondary थप्ने; form र list मा यी field लाई optional रूपमा show गर्ने।
