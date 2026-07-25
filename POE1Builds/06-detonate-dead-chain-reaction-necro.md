# Detonate Dead of Chain Reaction CI Necro (Endgame)

**Budget:** ~40–100 divines (mid endgame) → 150+ div (500–900M+ PoB range)  
**PoB DPS:** ~399M mid endgame / **300–900M+** documented range  
**Playstyle:** Two-button corpse explosion, ramping damage, facetank delving  
**Difficulty:** Medium–Hard

## Why This Build

Detonate Dead of Chain Reaction (`CDD`) is a corpse-scaling chaos build that ramps damage the longer you channel on a boss. Eva the Fairy's community template is league-start viable but scales to **hundreds of millions** of PoB DPS — up to **900M+** on higher loadouts — while using mostly achievable uniques and rares (no mirror weapon required for the 400M+ setup).

Strong for delving, pinnacle bosses, and players who like a methodical bossing playstyle.

## PoB Imports

| Loadout | Link | Reported DPS |
|---------|------|--------------|
| Endgame (3.25 reference) | [pobarchives.com/build/cNP9KGUr](https://pobarchives.com/build/cNP9KGUr) | ~399M |
| 3.28 template | [pobarchives.com/build/FgQcn4wC](https://pobarchives.com/build/FgQcn4wC) | ~28M league start → endgame loadouts in notes |
| Occultist variant | [pobb.in/st5gZMbCaysQ](https://pobb.in/st5gZMbCaysQ) | ~36M (different gearing) |

Import the 3.28 template; switch between loadouts in the PoB **Items** and **Notes** tabs for progression.

## Core Mechanic

1. **Detonate Dead of Chain Reaction** — explosions chain between corpses, multiplying damage in dense packs and on bosses with minion corpses.
2. **Chaos Inoculation** — immune to chaos damage; energy shield stacking for defence.
3. **Corpse generation** — desecrate, unearth, or minion deaths feed explosions.
4. **Two-button play** — typically maintain one skill (e.g. curse / corpse setup) while channeling or repeatedly casting DD; pseudo-channeling ramp.

## Core Skills

| Slot | Gem | Notes |
|------|-----|-------|
| Body 6L | Detonate Dead of Chain Reaction → Concentrated Effect → Controlled Destruction → Elemental Focus → Cruelty | Add Empower / Archmage variant per PoB |
| Curse | Despair / Temporal Chains on ring or gear | Apply via cast when needed |
| Corpses | Desecrate / Unearth / Spellslinger setup | Per PoB loadout |
| Auras | Determination, Grace, Defiance Banner (variants) | CI ES + armour/evasion |
| Minions | Raise Zombie / Spectre for corpses and buffs | Optional per loadout |

Exact links change between league-start and endgame — follow PoB notes.

## Ascendancy (Necromancer)

1. Essence Glutton / Plaguebringer (version-dependent)  
2. Corpse consumption or minion nodes  
3. Damage or defence notables per PoB  
4. Often **Mistress of Sacrifice** or bone armour variants for defence

Occultist variants trade defenses for curse scaling.

## Key Passive Tree

- **Keystone:** Chaos Inoculation  
- **ES stacking:** Int, ES wheels, Melding, Sovereignty  
- **Chaos / corpse damage:** area, chaos damage, mine/trap nodes if applicable  
- **Cluster jewels:** Chaos damage, Critical, Minion (mid–high budget)

## Budget Gear Priorities (~40–100 div)

| Slot | Target | Notes |
|------|--------|-------|
| Body | High ES rare or **Bramblejack** / **Incandescent Heart** (variant) | 6-link required for endgame |
| Weapon | +1–2 chaos gem wand or sceptre | Craft with essences |
| Shield | ES + spell block or **Saffell's Frame** | |
| Rings | ES, chaos res, cast speed | |
| Amulet | ES, chaos multi, skill gems | |
| Belt | ES, armour, flask slots | Stygian for jewel |

**Skip early:** Mageblood (author notes DPS and defence both improve with it, but not required for 399M PoB).

## Upgrade Path (100 → 250 div, 500–900M+)

1. Fractured ES chest or better 6-link base  
2. Cluster jewels (Chaos Damage, Critical, Minion Life)  
3. Better wand (+1 all chaos, cast speed, chaos multi)  
4. Forbidden Flame/Flesh  
5. Watcher's Eye + aura combo  
6. Mageblood (optional luxury)

## Defences

- CI + high ES pool (10k+ ES endgame)  
- Spell suppression / block layers per PoB  
- Facetank playstyle — designed to stand still and ramp  
- PoB EHP on 399M loadout: **~286k** effective hit pool

## PoB Calculation Notes

- CDD DPS is **burst + ramping** — PoB shows sustained peak, not first-second damage.  
- Corpse count and chain reaction must be configured in PoB (enemy settings, corpses nearby).  
- Some PoB versions need manual config for transfusion / festering resentment interactions (check notes).  
- Corpse explosion logic: `src/Modules/CalcOffence.lua`, skill data in `src/Data/Skills/`.

## Content Viability

- All uber pinnacle bosses (documented clears)  
- Delve 2000+ (build designed for delving)  
- Simulacrum / high-tier mapping (slower clear than Spark, stronger boss ramp)  
- Void stone collection

## Play Tips

1. Read the **Notes** section in the imported PoB — it is the primary guide.  
2. Practice corpse setup on dummies before ubers.  
3. Two-button rhythm: maintain corpses + curse, then DD.  
4. Not ideal for speed-clear mapping; shines on bosses and dense encounters.

## Honest Budget Note

The published **399M** PoB is below the 500M headline but sits in a documented **300–900M** scaling range. With cluster jewels and a better wand (~50–100 div more), community builders consistently push past **500M** in PoB. This is still far cheaper than mirror-tier Rage Vortex or Nightgrip ward stacks (~200–250 div minimum).
