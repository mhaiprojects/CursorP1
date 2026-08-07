# Cold Snap of Power Elementalist

**Source:** Path of Building game data (`repos/path-of-building/`)  
**Playstyle:** Power-charge cold spell spam, freeze, crit  
**Difficulty:** Medium

## Data-Driven Concept

This build is designed from the transfigured gem entry in PoB — not from a community PoB import. The skill's own constants define a power-charge loop; uniques in `src/Data/Uniques/` provide charge generation and spell scaling.

## Skill Data (PoB)

**Gem:** `Cold Snap of Power`  
**Files:** `src/Data/Gems.lua` (variant `ColdSnapAltX`), `src/Data/Skills/act_int.lua` (`skills["ColdSnapAltX"]`)

| Property | Value (from data) |
|----------|-------------------|
| Tags | `Critical, Spell, AoE, Cold` |
| Requirements | 40 Dex, 60 Int |
| Cooldown | 3 seconds |
| Cast time | 0.6s |
| Base crit | 10% |
| Freeze chance | 25% base |
| Power charge on crit | 50% (quality: +1% per gem quality) |
| Skill types | Spell, Area, Cold, Cooldown, Cascadable, Trappable, Totemable, Mineable |

**Mechanic (from description):** *"The cooldown can be bypassed by expending a Power Charge."*

Each crit has a 50% chance to refund a charge; spending a charge skips the 3s cooldown — enabling rapid repeat casts when charges are maintained.

## Scaling Tags (PoB)

From `skillTypes` and `stats` in `act_int.lua`:

- Cold spell hit damage (`spell_minimum/maximum_base_cold_damage`)
- Critical strikes (gem tagged `critical`)
- Area of effect (`is_area_damage`)
- Freeze (`base_chance_to_freeze_%`)
- Power charges (`add_power_charge_on_critical_strike_%`)

PoB calculation path: `src/Modules/CalcOffence.lua` (spell hits), `src/Modules/CalcSetup.lua` (charge buffs).

## Class & Ascendancy

**Witch → Elementalist** — matches `intelligence` gem tag and cold/ailment tree wheels.

Alternative: **Occultist** for curse scaling on frozen/cold-damaged enemies.

## Core Skills

| Slot | Gem | Data rationale |
|------|-----|----------------|
| Body 5–6L | Cold Snap of Power → Critical Damage → Elemental Focus → Hypothermia → (Increased AOE / Bonechill) | Cold + crit supports match skill tags |
| Utility | Frostblink, Shield Charge, Frost Shield | Int/dex hybrid requirement |
| Auras | Hatred, Wrath (optional), Determination | Elemental damage + defence |
| Curse | Frostbite or Elemental Weakness | Freeze / cold pen synergy |

## Uniques from PoB Data

| Item | File | Relevant mods |
|------|------|---------------|
| **Malachai's Loop** | `Uniques/shield.lua` | `+2 Maximum Power Charges`, `(12–16)% increased Spell Damage per Power Charge`, `20% chance to gain a Power Charge on Hit`, max-charge shock tradeoff |
| **The Aylardex** | `Uniques/amulet.lua` | `+1 Maximum Power Charges`, mana regen per charge |
| **Bitterdream** | `Uniques/mace.lua` | Budget sceptre: 6× level-15 cold supports (Bonechill, Hypothermia, Cold Pen, Added Cold, Inspiration) |
| **Doedre's Tenure** | `Uniques/gloves.lua` | Budget: `100% increased Spell Damage` |

## Rare Gear Priorities

- Weapon: +1–2 cold spell gems sceptre/wand (`src/Data/Bases/wand.lua`, `mace.lua`)
- Body: Life + ES hybrid (evasion/ES base from `Bases/body.lua`)
- Rings: Cold damage, cast speed, crit multi, +1 max power charge (crafted)

## Passive Tree (from PoB tree data)

- Cold damage wheels near Witch start
- Power charge nodes (`src/TreeData/` — search "Power Charge")
- Crit multiplier and spell damage
- **Cluster jewels** (`ClusterJewels.lua`): medium **Cold Damage** — notables include **Blizzard Caller**, **Deep Chill**

## Pantheons (from `Pantheons.lua`)

- **Major:** Soul of the Brine King — avoid freeze while casting cold spells
- **Minor:** Yugul — reflect mitigation for mapping

## Defences

- Life + ES hybrid (int/dex gear)
- Freeze enemies before they reach you
- Mind the Malachai's Loop shock at max charges — dump charges deliberately or use Brine King

## PoB Import Tip

Create a new build in Path of Building, add `Cold Snap of Power`, and paste gear from this guide. Verify DPS with **Power Charges** enabled in the Configuration tab — the skill's cooldown bypass depends on them.

## Data File Index

```
src/Data/Gems.lua              → ColdSnapAltX gem definition
src/Data/Skills/act_int.lua    → skill stats, cooldown, charge mechanics
src/Data/Uniques/shield.lua    → Malachai's Loop
src/Data/Uniques/mace.lua      → Bitterdream
src/Data/ClusterJewels.lua     → Cold cluster notables
src/Modules/CalcOffence.lua    → DPS calculation
```
