# Animate Guardian of Smiting (Endgame)

**Budget:** ~50–120 divines (budget itemization)  
**PoB DPS:** ~514M total (community min-maxed PoB, no Dissolution of Flesh)  
**Playstyle:** Minion tank + Smite buffs; you stay safe while AG melts bosses  
**Difficulty:** Medium–Hard

## Why This Build

Animate Guardian of Smiting (`AG of Smiting`) converts minion damage into one of the highest single-target DPS figures in POE1 on a **non-mirror** budget. A community PoB documents **514M DPS** while deliberately cutting expensive gear (budget Kaom's Heart, no premium belt). The build uses `Smite` buffs on a massively scaled Animate Guardian that attacks for you.

Ideal if you want boss-killing power without playing a glass cannon caster.

## PoB Import

| Source | Link | Reported DPS |
|--------|------|--------------|
| Budget-minmax PoB | [pobarchives.com/build/rkeFm98T](https://pobarchives.com/build/rkeFm98T) | ~514M |

Author notes (from PoB comments): you do **not** need Acceleration + premium belt; a ~1200 life Kaom's Heart cuts cost significantly. **100% Adorned** (2× 2% max fire res jewels) is strongly recommended for itemization.

## Core Mechanic

1. **Animate Guardian** — geared with high-damage uniques and rares; buffed via `Feeding Frenzy` on AG (not spectres) so AG does not get stuck in defensive AI.
2. **Smite of the Arena** — provides aura-like buffs to you and AG; scales with minion damage and attack speed.
3. **Soul Eater stacks** — multiple minions share stacks; for hard content, pre-stack via `Automation` + `Lifetap` trick (~4.5s to max).
4. **Fortify** — `Forbidden Flame`/`Forbidden Flesh` for Fortitude is near-mandatory so AG survives without Hallowed Monarch.

## Core Skills

| Slot | Gem | Notes |
|------|-----|-------|
| Body | Smite of the Arena → Multistrike → Brutality → Melee Physical → Empower | Main damage link on AG setup |
| AG 4L+ | Animate Guardian → Feeding Frenzy → Minion Damage → Melee Physical | Feeding Frenzy on AG is critical |
| Spectres | Raise Spectre → Minion Life → Elemental Army → (utility) | Frenzy / Power charge sources |
| Auras | Determination, Pride, Defiance Banner | Armour + impale scaling |
| Utility | Convocation, Vigilant Strike (fortify), Automation | |

Exact gem layout varies by PoB loadout — import the archive link above.

## Ascendancy

Typically **Guardian** (for AG scaling and defenses) or **Champion** (fortify / impale variants). Follow the imported PoB ascendancy path.

## Budget Gear Priorities (~50–120 div)

| Slot | Budget Cut | Keep / Splurge |
|------|------------|----------------|
| Body | Kaom's Heart ~1200 life (not 1500+ roll) | Life + fire res for AG |
| Belt | Skip "Massive" premium belt; use life/res rare | |
| Jewels | **100% Adorned** — two 2% max fire res jewels | Non-negotiable per author |
| AG Gear | Kingmaker, Winds of Change, rare high-armour pieces | AG gear is the real DPS |
| Forbidden | Fortitude jewels for fortify on AG | Needed for AG survival |
| Voices | Optional 1-socket Voices; skip recovery mastery for +5% life node | |

### AG Unique Targets (buy over time)

- **Kingmaker** (axe) — crit multi + AG damage  
- **Winds of Change** (quiver) — minion damage  
- **Leper's Alms** or high-block shield  
- **Garb of the Ephemeral** or high-ES body for AG (depends on PoB version)

## Upgrade Path (120 → 250 div)

1. Better rolled Kaom's / rare chest with more life  
2. Second Voices or large cluster jewels  
3. Better forbidden jewel rolls  
4. Premium AG weapon swap (higher DPS uniques)  
5. Mageblood (luxury — not required for 514M PoB)

## Defences

- You: high life, block, armour, fortify  
- AG: geared as tank with high armour/ES; `Convocation` for emergencies  
- Phys max hit in PoB: **~50k+** on documented loadout

## PoB Calculation Notes

- DPS is primarily **Animate Guardian attack DPS** buffed by Smite.  
- Soul Eater stacks materially affect DPS — configure correctly in PoB.  
- "Paper DPS" is lower if Feeding Frenzy is on spectres instead of AG; author recommends AG for real-world performance.  
- Minion mods parse through `src/Modules/CalcSetup.lua` and `src/Data/Minions.lua` in Path of Building.

## Content Viability

- Uber bosses (documented 514M PoB)  
- Valdo's Proving Grounds (hard waves — pre-stack Soul Eater)  
- High-tier mapping while AG clears bosses  
- Not a speed-farming mapper; excels at single target

## Play Tips

1. Gear AG before investing in your own DPS slots.  
2. Use Convocation when AG takes big hits.  
3. For Valdos / pinnacle bosses, pre-stack Soul Eater with Automation trick.  
4. Import PoB and use the notes section for gem and gear progression loadouts.
